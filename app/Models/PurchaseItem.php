<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    protected static function booted()
    {
        // ========== BEFORE SAVING ==========
        static::saving(function ($item) {

            // Hitung subtotal aman
            $qty   = max(0, (float) $item->quantity);
            $price = max(0, (float) $item->price);

            $item->subtotal = $qty * $price;

            if ($qty <= 0) {
                throw new \Exception("Jumlah pembelian tidak boleh 0 atau negatif.");
            }

            if ($price <= 0) {
                throw new \Exception("Harga beli harus lebih besar dari 0.");
            }
        });

        // ========== CREATED ==========
        static::created(function ($item) {

            DB::transaction(function () use ($item) {

                $product = $item->product;

                if ($product) {

                    // Tambahkan stok
                    $product->increment('stock', $item->quantity);

                    // VALIDASI — stok tidak boleh negatif
                    if ($product->stock < 0) {
                        throw new \Exception("Stok menjadi negatif setelah pembelian. Transaksi dibatalkan.");
                    }

                    // Update harga beli, jual, holding cost
                    $product->purchase_price = $item->price;

                    // Markup tetap aman 20%
                    $product->sale_price = round($item->price * 1.20, 0);

                    // Holding cost 20%
                    $product->holding_cost = round($item->price * 0.20, 0);

                    $product->save();
                }

                // Update total pembelian
                $item->purchase->updateTotal();
            });
        });

        // ========== UPDATED ==========
        static::updated(function ($item) {

            DB::transaction(function () use ($item) {

                $product = $item->product;

                if ($product) {

                    // Update stok jika qty berubah
                    if ($item->isDirty('quantity')) {

                        $oldQty = $item->getOriginal('quantity');
                        $newQty = $item->quantity;
                        $diff   = $newQty - $oldQty;

                        if ($diff > 0) {
                            $product->increment('stock', $diff);
                        } else {
                            $product->decrement('stock', abs($diff));
                        }

                        if ($product->stock < 0) {
                            throw new \Exception("Perubahan jumlah membuat stok negatif. Transaksi dibatalkan.");
                        }
                    }

                    // Update harga beli & jual jika harga berubah
                    if ($item->isDirty('price')) {
                        $product->purchase_price = $item->price;
                        $product->sale_price = round($item->price * 1.20, 0);
                        $product->holding_cost = round($item->price * 0.20, 0);
                        $product->save();
                    }
                }

                // Update total pembelian
                $item->purchase->updateTotal();
            });
        });

        // ========== DELETED ==========
        static::deleted(function ($item) {

            DB::transaction(function () use ($item) {

                $product = $item->product;

                if ($product) {

                    // Kurangi stok
                    $product->decrement('stock', $item->quantity);

                    if ($product->stock < 0) {
                        throw new \Exception("Stok menjadi negatif setelah penghapusan. Transaksi dibatalkan.");
                    }
                }

                // Update total pembelian
                $item->purchase->updateTotal();
            });
        });
    }
}