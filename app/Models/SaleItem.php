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

    protected static function booted()
    {
        /*
        |--------------------------------------------------------------------------
        | BEFORE SAVE → Hitung subtotal
        |--------------------------------------------------------------------------
        */
        static::saving(function ($item) {
            $qty   = max(0, (int) $item->quantity);
            $price = max(0, (int) $item->price);

            $item->subtotal = $qty * $price;

            if ($qty <= 0) {
                throw new \Exception("Jumlah penjualan harus lebih dari 0.");
            }
        });

        /*
        |--------------------------------------------------------------------------
        | BEFORE CREATE → Validasi stok cukup
        |--------------------------------------------------------------------------
        */
        static::creating(function ($item) {
            $product = $item->product;

            if ($product) {
                if ($product->stock < $item->quantity) {
                    throw new \Exception("Stok {$product->name} tidak cukup untuk penjualan.");
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | AFTER CREATE → Kurangi stok
        |--------------------------------------------------------------------------
        */
        static::created(function ($item) {
            $product = $item->product;

            if ($product) {
                $product->decrement('stock', $item->quantity);

                if ($product->stock < 0) {
                    throw new \Exception("Stok {$product->name} menjadi negatif! Transaksi dibatalkan.");
                }
            }

            $item->sale->updateTotal();
        });

        /*
        |--------------------------------------------------------------------------
        | BEFORE UPDATE → Cek apakah stok cukup jika quantity berubah
        |--------------------------------------------------------------------------
        */
        static::updating(function ($item) {
            $product = $item->product;

            if ($product && $item->isDirty('quantity')) {
                $oldQty = (int) $item->getOriginal('quantity');
                $newQty = (int) $item->quantity;
                $diff   = $newQty - $oldQty;

                if ($diff > 0 && $product->stock < $diff) {
                    throw new \Exception("Stok {$product->name} tidak cukup untuk perubahan jumlah.");
                }
            }
        });

        /*
        |--------------------------------------------------------------------------
        | AFTER UPDATE → Sesuaikan stok
        |--------------------------------------------------------------------------
        */
        static::updated(function ($item) {
            $product = $item->product;

            if ($product && $item->isDirty('quantity')) {

                $oldQty = (int) $item->getOriginal('quantity');
                $newQty = (int) $item->quantity;
                $diff   = $newQty - $oldQty;

                if ($diff > 0) {
                    // tambah qty → stok berkurang
                    $product->decrement('stock', $diff);
                } else {
                    // kurangi qty → stok kembali
                    $product->increment('stock', abs($diff));
                }

                if ($product->stock < 0) {
                    throw new \Exception("Stok {$product->name} menjadi negatif setelah update.");
                }
            }

            $item->sale->updateTotal();
        });

        /*
        |--------------------------------------------------------------------------
        | AFTER DELETE → Kembalikan stok
        |--------------------------------------------------------------------------
        */
        static::deleted(function ($item) {
            $product = $item->product;

            if ($product) {
                $product->increment('stock', $item->quantity);

                if ($product->stock < 0) {
                    throw new \Exception("Penghapusan membuat stok negatif. Tidak diperbolehkan.");
                }
            }

            $item->sale->updateTotal();
        });
    }
}