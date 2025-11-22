<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'sale_date',
        'total_amount',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    // Hitung total otomatis dari items
    public function updateTotal()
    {
        $this->total_amount = $this->items()->sum('subtotal');
        $this->saveQuietly();
    }

    protected static function booted()
    {
        static::creating(function ($sale) {
            // user
            if (Auth::check() && empty($sale->user_id)) {
                $sale->user_id = Auth::id();
            }

            // invoice
            if (empty($sale->invoice_number)) {
                $latest = static::whereDate('created_at', today())->max('invoice_number');
                $lastNumber = $latest ? (int) substr($latest, -4) : 0;
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                $sale->invoice_number = 'PJ-' . now()->format('Ymd') . '-' . $nextNumber;
            }

            // tanggal
            if (empty($sale->sale_date)) {
                $sale->sale_date = now();
            }
        });

        // Kalau SATU transaksi penjualan DIHAPUS,
        // stok semua item HARUS kembali.
        // Ini yang memperbaiki kasus: stok 28 → jual 1 (27) → hapus penjualan → stok kembali 28.
        static::deleting(function ($sale) {
            foreach ($sale->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }
        });
    }
}