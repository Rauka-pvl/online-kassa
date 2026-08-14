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
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Primary subcatalog accessor (breadcrumbs / search default).
     * Eager-load `subCatalogs.catalog` instead of `subCatalog`.
     */
    public function getSubCatalogAttribute(): ?SubCatalog
    {
        $links = $this->relationLoaded('subCatalogs')
            ? $this->subCatalogs
            : $this->subCatalogs()->with('catalog')->get();

        $primary = $links->first(function (SubCatalog $sub) {
            return (bool) ($sub->pivot->is_primary ?? false);
        });

        return $primary ?? $links->first();
    }

    public function getSubCatalogIdAttribute($value): ?int
    {
        if (array_key_exists('sub_catalog_id', $this->attributes) && $this->attributes['sub_catalog_id'] !== null) {
            return (int) $this->attributes['sub_catalog_id'];
        }

        return $this->subCatalog?->id;
    }

    public function getPrimarySubCatalogIdAttribute(): ?int
    {
        return $this->sub_catalog_id;
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
     * Sync pivot bindings and mark one primary.
     *
     * @param  array<int, int|string>  $subCatalogIds
     * @param  int|string|null  $primarySubCatalogId
     */
    public function syncSubCatalogs(array $subCatalogIds, int|string|null $primarySubCatalogId = null): void
    {
        $ids = collect($subCatalogIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            throw new \InvalidArgumentException('Service must be linked to at least one subcatalog.');
        }

        $primary = $primarySubCatalogId !== null
            ? (int) $primarySubCatalogId
            : $ids->first();

        if (!$ids->contains($primary)) {
            $ids->push($primary);
            $ids = $ids->unique()->values();
        }

        $sync = [];
        foreach ($ids as $id) {
            $sync[$id] = ['is_primary' => $id === $primary];
        }

        $this->subCatalogs()->sync($sync);

        if (Schema::hasColumn($this->getTable(), 'sub_catalog_id')) {
            $this->forceFill(['sub_catalog_id' => $primary])->save();
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
}
