<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StorageLocation extends Model {
    use HasFactory;
    protected $fillable = ['name','code','description','is_active'];
    public function products(){ return $this->hasMany(Product::class, 'storage_location_id'); }
}
