<?php

namespace App\Services;

use App\Models\PurchaseItem;

class PricePredictionService
{
    public static function predictPrice(int $productId, int $period = 6): ?float
    {
        // Ambil harga dari tabel purchase_items
        $prices = PurchaseItem::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->take($period)
            ->pluck('price');

        // Jika tidak ada data pembelian, kembalikan null
        if ($prices->isEmpty()) {
            return null;
        }

        // Hitung rata-rata harga pembelian
        return round($prices->avg(), 2);
    }
}
