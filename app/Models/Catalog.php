<?php
// App\Models\Catalog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function subCatalogs()
    {
        return $this->hasMany(SubCatalog::class);
    }

    public function services()
    {
        return $this->hasManyThrough(Service::class, SubCatalog::class);
    }
}
