<?php
// app/Models/Service.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * All subcatalogs this service is shown under (unlimited).
     */
    public function subCatalogs(): BelongsToMany
    {
        return $this->belongsToMany(SubCatalog::class, 'service_sub_catalog')
            ->withTimestamps();
    }

    /**
     * First linked subcatalog (breadcrumbs / search fallback).
     * Eager-load `subCatalogs.catalog` instead of `subCatalog`.
     */
    public function getSubCatalogAttribute(): ?SubCatalog
    {
        $links = $this->relationLoaded('subCatalogs')
            ? $this->subCatalogs
            : $this->subCatalogs()->with('catalog')->get();

        return $links->first();
    }

    public function getSubCatalogIdAttribute($value): ?int
    {
        if (array_key_exists('sub_catalog_id', $this->attributes) && $this->attributes['sub_catalog_id'] !== null) {
            return (int) $this->attributes['sub_catalog_id'];
        }

        return $this->subCatalog?->id;
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'schedule_services');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'service_id');
    }

    public function hasTimeSlots(): bool
    {
        return !is_null($this->duration) && $this->duration > 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, '.', ' ') . ' ₸';
    }

    /**
     * Sync pivot bindings.
     *
     * @param  array<int, int|string>  $subCatalogIds
     */
    public function syncSubCatalogs(array $subCatalogIds): void
    {
        $ids = collect($subCatalogIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            throw new \InvalidArgumentException('Service must be linked to at least one subcatalog.');
        }

        $this->subCatalogs()->sync($ids->all());

        if (Schema::hasColumn($this->getTable(), 'sub_catalog_id')) {
            $this->forceFill(['sub_catalog_id' => $ids->first()])->save();
        }
    }

    public function linkedSubCatalogLabels(): Collection
    {
        $links = $this->relationLoaded('subCatalogs')
            ? $this->subCatalogs
            : $this->subCatalogs()->with('catalog')->get();

        return $links->map(function (SubCatalog $sub) {
            $catalogName = optional($sub->catalog)->name;

            return trim(($catalogName ? $catalogName . ' → ' : '') . $sub->name);
        });
    }

    /**
     * Resolve booking/search context to a linked subcatalog.
     */
    public function resolveSubCatalog(?int $subCatalogId = null): ?SubCatalog
    {
        $links = $this->relationLoaded('subCatalogs')
            ? $this->subCatalogs
            : $this->subCatalogs()->with('catalog')->get();

        if ($subCatalogId) {
            $match = $links->firstWhere('id', $subCatalogId);
            if ($match) {
                if (!$match->relationLoaded('catalog')) {
                    $match->load('catalog');
                }

                return $match;
            }

            return null;
        }

        if ($links->count() === 1) {
            $only = $links->first();
            if ($only && !$only->relationLoaded('catalog')) {
                $only->load('catalog');
            }

            return $only;
        }

        return null;
    }

    /**
     * Active doctor schedules for this service, optionally scoped to a direction.
     */
    public function activeSchedulesForSubCatalog(?int $subCatalogId = null): Collection
    {
        $schedules = $this->schedules()
            ->where('schedules.is_active', true)
            ->whereHas('user', function ($q) {
                $q->where('role', 4)->where('is_active', true);
            })
            ->with(['user', 'services' => function ($q) {
                $q->where('services.is_active', true)->with(['subCatalogs.catalog']);
            }])
            ->get();

        if (!$subCatalogId) {
            return $schedules->unique('user_id')->values();
        }

        $subCatalog = $this->resolveSubCatalog($subCatalogId);
        if (!$subCatalog) {
            return collect();
        }

        return $schedules
            ->filter(fn (Schedule $schedule) => $this->scheduleMatchesSubCatalog($schedule, $subCatalog))
            ->unique('user_id')
            ->values();
    }

    public function scheduleMatchesSubCatalog(Schedule $schedule, SubCatalog $subCatalog): bool
    {
        $services = $schedule->relationLoaded('services')
            ? $schedule->services
            : $schedule->services()->where('services.is_active', true)->with('subCatalogs')->get();

        $hasOtherInDirection = $services
            ->where('id', '!=', $this->id)
            ->contains(function (Service $svc) use ($subCatalog) {
                $links = $svc->relationLoaded('subCatalogs')
                    ? $svc->subCatalogs
                    : $svc->subCatalogs()->get();

                return $links->contains('id', $subCatalog->id);
            });

        if ($hasOtherInDirection) {
            return true;
        }

        $specialization = mb_strtolower(trim((string) optional($schedule->user)->specialization));
        if ($specialization === '') {
            return false;
        }

        $candidates = array_filter([
            mb_strtolower(trim($subCatalog->name)),
            mb_strtolower(trim((string) optional($subCatalog->catalog)->name)),
        ]);

        foreach ($candidates as $name) {
            if ($name !== '' && (str_contains($specialization, $name) || str_contains($name, $specialization))) {
                return true;
            }
        }

        return false;
    }
}
