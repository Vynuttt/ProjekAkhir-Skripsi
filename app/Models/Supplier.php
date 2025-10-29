<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model {
    use HasFactory;
    protected $fillable = ['name','phone','email','address','is_active'];
    public function purchases(){ return $this->hasMany(Purchase::class); }
    public function products(){ return $this->hasMany(Product::class); }
}
