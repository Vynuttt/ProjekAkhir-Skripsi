<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'brand_id',
        'category_id',
        'supplier_id',
        'storage_location_id',
        'stock',
        'purchase_price',
        'sale_price',
        'is_active',
        // field tambahan untuk EOQ:
        'ordering_cost',    // biaya pemesanan per order
        'holding_cost',     // biaya penyimpanan per unit per tahun
        'annual_demand',    // permintaan tahunan (estimasi / realisasi)
        'reorder_point',    // titik pemesanan ulang
        'safety_stock',     // stok pengaman
        'lead_time',       // lead time dalam hari
    ];

    /* ==============================
     *  RELATIONSHIP
     * ============================== */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function storageLocation()
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }

    public function salesReturns()
    {
    return $this->hasMany(SalesReturn::class);
    }


    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // perhitungan EOQ
    public function calculateEOQ(): int
    {
        $D = (float) ($this->annual_demand ?? 0);  // permintaan tahunan
        $S = (float) ($this->ordering_cost ?? 0);  // biaya pemesanan
        $H = (float) ($this->holding_cost ?? 0);   // biaya penyimpanan per unit per tahun

        if ($D <= 0 || $S <= 0 || $H <= 0) {
            return 0;
        }

        return (int) ceil(sqrt((2 * $D * $S) / $H));
    }

    /**
     * Hitung Reorder Point otomatis jika ada lead time & demand harian
     */
    public function calculateReorderPoint(): int
    {
        $dailyDemand = $this->annual_demand > 0 ? ceil($this->annual_demand / 365) : 0;
        $leadTime = $this->lead_time ?? 0;
        $rop = ($dailyDemand * $leadTime) + ($this->safety_stock ?? 0);

        return max($rop, 0);
    }


    /* ==============================
     *  ACCESSOR
     * ============================== */
    public function getEoqAttribute(): int
    {
        return $this->calculateEOQ();
    }

    public function getReorderPointAttribute($value): int
    {
        return !is_null($value)
            ? (int) $value
            : $this->calculateReorderPoint();
    }
}
