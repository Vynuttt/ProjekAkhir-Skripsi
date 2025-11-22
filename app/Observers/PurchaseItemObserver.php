<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Services\PricingService;

class PurchaseItemObserver
{
    public function created(PurchaseItem $item): void
    {
        // Update master produk dengan harga beli terakhir + harga jual (markup)
        $product = $item->product; // pastikan relasi product() ada di PurchaseItem
        if ($product && $item->price > 0) {
            PricingService::applyPurchaseImpact($product, (float) $item->price);
        }
    }

    public function updated(PurchaseItem $item): void
    {
        // Kalau harga item pembelian diubah, kita sinkronkan lagi
        $product = $item->product;
        if ($product && $item->price > 0) {
            PricingService::applyPurchaseImpact($product, (float) $item->price);
        }
    }
}
