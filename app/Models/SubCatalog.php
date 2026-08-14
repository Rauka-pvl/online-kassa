<?php
// app/Models/SubCatalog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'catalog_id'
    ];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    /**
     * Services linked via pivot (supports multiple subcatalogs per service).
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_sub_catalog')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
