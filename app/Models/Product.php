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

        // EOQ 
        'ordering_cost',
        'holding_cost',
        'annual_demand',
        'reorder_point',
        'safety_stock',
        'lead_time',
        'eoq',
    ];

    // =========================
    // RELASI
    // =========================

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

    // =========================
    // PERHITUNGAN EOQ / ROP
    // =========================

    public function calculateHoldingCost(float $percentage = 0.2): float
    {
        if ($this->purchase_price > 0) {
            return round($this->purchase_price * $percentage, 2);
        }
        return 0;
    }

    public function calculateEOQ(): int
    {
        $D = (float) ($this->annual_demand ?? 0);
        $S = (float) ($this->ordering_cost ?? 0);
        $H = (float) ($this->holding_cost ?? $this->calculateHoldingCost());

        if ($D <= 0 || $S <= 0 || $H <= 0) {
            return 0;
        }

        return (int) ceil(sqrt((2 * $D * $S) / $H));
    }

    public function calculateReorderPoint(): int
    {
        $dailyDemand = $this->annual_demand > 0
            ? ceil($this->annual_demand / 365)
            : 0;

        $leadTime = $this->lead_time ?? 0;

        $rop = ($dailyDemand * $leadTime) + ($this->safety_stock ?? 0);

        return max($rop, 0);
    }

    // cycle time 

    public function calculateCycleTime(): ?int
    {
        $eoq = $this->calculateEOQ();
        $dailyDemand = $this->annual_demand > 0 ? $this->annual_demand / 365 : 0;

        if ($eoq <= 0 || $dailyDemand <= 0) {
            return null;
        }

        return (int) ceil($eoq / $dailyDemand);
    }

    public function getCycleTimeAttribute()
    {
        return $this->calculateCycleTime();
    }

    protected static function booted(): void
    {
        static::saving(function ($product) {

            if ($product->isDirty('purchase_price') && $product->purchase_price > 0) {

                $markup = 20; 
                $product->sale_price = round(
                    $product->purchase_price * (1 + ($markup / 100)),
                    0
                );

                if (is_null($product->holding_cost) || $product->holding_cost == 0) {
                    $product->holding_cost = round($product->purchase_price * 0.20, 0);
                }
            }

            $eoq = $product->calculateEOQ();
            $product->eoq = $eoq > 0 ? $eoq : null;

            $rop = $product->calculateReorderPoint();
            $product->reorder_point = $rop > 0 ? $rop : null;
        });
    }

    public function getEoqAttribute($value): int
    {
        return $value ?? $this->calculateEOQ();
    }

    public function getReorderPointAttribute($value): int
    {
        return $value ?? $this->calculateReorderPoint();
    }
}