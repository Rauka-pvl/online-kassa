<?php
// app/Models/SubCatalog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'sub_catalog_id');
    }
}
