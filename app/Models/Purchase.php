<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',    // Nomor transaksi 
        'supplier_id',       
        'user_id',           
        'purchase_date',     
        'total_amount'       
    ];

    //relasi

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Hitung total otomatis dari items
    public function updateTotal()
    {
        $this->total_amount = $this->items()->sum('subtotal');
        $this->saveQuietly(); // Save tanpa trigger event tambahan
    }

    // Boot method untuk model
    protected static function booted()
    {
        static::creating(function ($purchase) {
            // isi otomatis user_id dari user login
            if (Auth::check() && empty($purchase->user_id)) {
                $purchase->user_id = Auth::id();
            }

            // Auto generate nomor invoice/transaksi jika belum diisi
            if (empty($purchase->invoice_number)) {
                $latest = static::whereDate('created_at', today())->max('invoice_number');
                $lastNumber = $latest ? (int) substr($latest, -4) : 0;
                $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

                // Format: PB-YYYYMMDD-0001 (PB = Pembelian)
                $purchase->invoice_number = 'PB-' . now()->format('Ymd') . '-' . $nextNumber;
            }

            // Set tanggal transaksi otomatis jika belum ada
            if (empty($purchase->purchase_date)) {
                $purchase->purchase_date = now();
            }
        });
    }
}
