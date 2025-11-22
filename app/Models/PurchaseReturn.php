<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
        'reason',
        'return_date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted()
    {
        static::saving(function ($return) {

            // CEGAH ERROR RELATION NULL (Filament belum populate)
            if (!$return->purchase_id || !$return->product_id) {
                return;
            }

            // VALIDASI: Tidak boleh retur melebihi jumlah dibeli

            $purchasedQty = \App\Models\PurchaseItem::where('purchase_id', $return->purchase_id)
                ->where('product_id', $return->product_id)
                ->sum('quantity');

            $returnedQtyBefore = \App\Models\PurchaseReturn::where('purchase_id', $return->purchase_id)
                ->where('product_id', $return->product_id)
                ->where('id', '!=', $return->id ?? 0)   // exclude current row if editing
                ->sum('quantity');

            $availableToReturn = $purchasedQty - $returnedQtyBefore;

            if ($return->quantity > $availableToReturn) {
                throw new \Exception("Jumlah retur melebihi jumlah pembelian sebenarnya. Maksimal dapat diretur: $availableToReturn unit.");
            }

            // SET unit_price berdasarkan harga beli terakhir
            $latestPurchasePrice = \App\Models\PurchaseItem::where('product_id', $return->product_id)
                ->orderByDesc('created_at')
                ->value('price');

            if ($latestPurchasePrice) {
                $return->unit_price = $latestPurchasePrice;
            }

            // Hitung total
            $return->total = (float) $return->quantity * (float) $return->unit_price;

            // Validasi Stok
            if ($return->quantity > $return->product->stock) {
                throw new \Exception("Stok {$return->product->name} tidak mencukupi untuk retur pembelian.");
            }
        });

        // Created : stok berkurang
        static::created(function ($return) {
            $return->product->decrement('stock', $return->quantity);
        });

        // Updated : Sesuaikan perubahan qty
        static::updated(function ($return) {
            $product = $return->product;

            $oldQty = (float) $return->getOriginal('quantity');
            $newQty = (float) $return->quantity;
            $diff = $newQty - $oldQty;

            if ($diff > 0) {
                // retur ditambah → stok dikurangi lagi
                if ($diff > $product->stock) {
                    throw new \Exception("Stok {$product->name} tidak cukup untuk perubahan retur.");
                }
                $product->decrement('stock', $diff);
            } elseif ($diff < 0) {
                // retur dikurangi → stok dikembalikan
                $product->increment('stock', abs($diff));
            }

            // hitung total ulang
            $return->total = (float) $return->quantity * (float) ($return->unit_price ?? 0);
            $return->saveQuietly();
        });

        // event delete
        static::deleted(function ($return) {
            $return->product->increment('stock', $return->quantity);
        });
    }
}