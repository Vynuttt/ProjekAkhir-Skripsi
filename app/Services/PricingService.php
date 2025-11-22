<?php

namespace App\Services;

use App\Models\Product;

class PricingService
{
    // markup 20% (bisa kamu taruh di .env / settings table kalau mau fleksibel)
    public const DEFAULT_MARKUP_PERCENT = 20;

    public static function computeSalePrice(float $purchasePrice, ?float $markupPercent = null): float
    {
        $m = $markupPercent ?? self::DEFAULT_MARKUP_PERCENT;
        return round($purchasePrice * (1 + ($m/100)), 0);
    }

    public static function applyPurchaseImpact(Product $product, float $lastPurchasePrice, ?float $markupPercent = null): void
    {
        // Update harga beli ke harga pembelian terbaru
        $product->purchase_price = $lastPurchasePrice;

        // Optional: jika holding_cost kamu otomatis (misal 20% dari harga beli per tahun)
        if (is_null($product->holding_cost) || $product->holding_cost <= 0) {
            $product->holding_cost = round($lastPurchasePrice * 0.20, 0); // sesuaikan kalau ada aturan lain
        }

        // Hitung harga jual dari markup (default 20%)
        $product->sale_price = self::computeSalePrice($lastPurchasePrice, $markupPercent);

        // Simpan -> akan memicu perhitungan EOQ/ROP otomatis lewat booted() di Product
        $product->save();
    }
}
