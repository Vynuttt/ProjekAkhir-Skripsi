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
        'unit_price',
        'total',
        'reason',
        'return_type',
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

    protected static function booted()
    {
        static::saving(function ($return) {

            if (!$return->sale_id || !$return->product_id) return;

            $qty   = (float) ($return->quantity ?? 0);
            $price = (float) ($return->unit_price ?? 0);

            $return->total = $qty * $price;

            // Hitung jumlah penjualan
            $soldQty = SaleItem::where('sale_id', $return->sale_id)
                ->where('product_id', $return->product_id)
                ->sum('quantity');

            // Hitung retur sebelumnya
            $previousReturns = SalesReturn::where('sale_id', $return->sale_id)
                ->where('product_id', $return->product_id)
                ->where('id', '!=', ($return->id ?? 0)) // hindari hitung diri sendiri
                ->sum('quantity');

            // Maksimum retur yang masih diperbolehkan 
            $maxAllowed = $soldQty - $previousReturns;

            if ($qty > $maxAllowed) {
                throw new \Exception(
                    "Total retur melebihi batas. Sudah diretur: $previousReturns unit. ".
                    "Maksimal tambahan retur yang diperbolehkan hanya $maxAllowed unit."
                );
            }
        });

        static::created(function ($return) {
            $product = $return->product;
            if ($product) {
                // Return = barang kembali ke stok
                $product->increment('stock', $return->quantity);
            }
        });

        static::updated(function ($return) {
            $product = $return->product;

            if ($product && $return->isDirty('quantity')) {
                $oldQty = (float) $return->getOriginal('quantity');
                $newQty = (float) $return->quantity;
                $diff = $newQty - $oldQty;

                if ($diff > 0) {
                    $product->increment('stock', $diff);
                } elseif ($diff < 0) {
                    $product->decrement('stock', abs($diff));
                }
            }

            // update total
            $qty   = (float) $return->quantity;
            $price = (float) ($return->unit_price ?? 0);
            $return->total = $qty * $price;
        });

        static::deleted(function ($return) {
            $product = $return->product;
            if ($product) {
                $product->decrement('stock', $return->quantity);
            }
        });
    }
}