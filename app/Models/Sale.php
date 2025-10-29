<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',    // Nomor transaksi 
        'user_id',           
        'sale_date',         
        'total_amount'       
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

    // Boot method untuk model
    protected static function booted()
    {
        static::creating(function ($sale) {
            // Isi otomatis user_id dari user login
            if (Auth::check() && empty($sale->user_id)) {
                $sale->user_id = Auth::id();
            }

            // Auto generate nomor invoice jika belum diisi
            if (empty($sale->invoice_number)) {
                $latest = static::whereDate('created_at', today())->max('invoice_number');
                $lastNumber = $latest ? (int) substr($latest, -4) : 0;
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                // Format: PJ-YYYYMMDD-0001 (PJ = Penjualan)
                $sale->invoice_number = 'PJ-' . now()->format('Ymd') . '-' . $nextNumber;
            }

            // Set tanggal transaksi otomatis jika belum ada
            if (empty($sale->sale_date)) {
                $sale->sale_date = now();
            }
        });
    }
}
