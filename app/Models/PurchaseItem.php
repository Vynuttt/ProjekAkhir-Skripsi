<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // 🔹 Event untuk update subtotal, stok produk, dan total purchase
    protected static function booted()
    {
        static::saving(function ($item) {
            // hitung subtotal otomatis
            $item->subtotal = $item->quantity * $item->price;
        });

        static::created(function ($item) {
            // tambah stok produk saat pembelian
            $item->product->increment('stock', $item->quantity);

            // update total purchase
            $item->purchase->updateTotal();
        });

        static::updated(function ($item) {
            if ($item->isDirty('quantity')) {
                $oldQty = $item->getOriginal('quantity');
                $diff = $item->quantity - $oldQty;

                // update stok sesuai selisih qty
                $item->product->increment('stock', $diff);
            }

            // update total purchase
            $item->purchase->updateTotal();
        });

        static::deleted(function ($item) {
            // rollback stok kalau item dihapus
            $item->product->decrement('stock', $item->quantity);

            // update total purchase
            $item->purchase->updateTotal();
        });
    }
}
