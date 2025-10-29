<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'reason',
        'return_date',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 🔁 Otomatis update stok setelah retur dibuat
    protected static function booted()
    {
        static::created(function ($salesReturn) {
            $product = $salesReturn->product;
            if ($product) {
                $product->increment('stock', $salesReturn->quantity);
            }
        });

        static::deleted(function ($salesReturn) {
            $product = $salesReturn->product;
            if ($product) {
                $product->decrement('stock', $salesReturn->quantity);
            }
        });
    }
}
