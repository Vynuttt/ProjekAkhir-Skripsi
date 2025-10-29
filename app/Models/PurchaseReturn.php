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
    static::creating(function ($return) {
        $return->total = $return->quantity * $return->unit_price;
    });

    static::created(function ($return) {
        // Kurangi stok setelah retur
        $return->product->decrement('stock', $return->quantity);
    });
}


}
