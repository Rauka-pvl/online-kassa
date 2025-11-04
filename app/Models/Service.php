<?php
// app/Models/Service.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub_catalog_id',
        'description',
        'price',
        'duration',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subCatalog(): BelongsTo
    {
        return $this->belongsTo(SubCatalog::class);
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
}
