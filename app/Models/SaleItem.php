<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 🔹 Event untuk update subtotal, stok produk, dan total sale
    protected static function booted()
    {
        static::saving(function ($item) {
            // hitung subtotal otomatis
            $item->subtotal = $item->quantity * $item->price;
        });

        static::created(function ($item) {
            // kurangi stok produk saat penjualan
            $item->product->decrement('stock', $item->quantity);

            // update total sale
            $item->sale->updateTotal();
        });

        static::updated(function ($item) {
            if ($item->isDirty('quantity')) {
                $oldQty = $item->getOriginal('quantity');
                $diff = $item->quantity - $oldQty;

                // update stok sesuai selisih qty (positif → tambah, negatif → kurang)
                $item->product->decrement('stock', $diff);
            }

            // update total sale
            $item->sale->updateTotal();
        });

        static::deleted(function ($item) {
            // rollback stok kalau item penjualan dihapus
            $item->product->increment('stock', $item->quantity);

            // update total sale
            $item->sale->updateTotal();
        });
    }
}
