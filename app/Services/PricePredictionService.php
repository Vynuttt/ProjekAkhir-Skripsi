<?php

namespace App\Services;

use App\Models\PurchaseItem;

class PricePredictionService
{
    // Ambil harga dari tabel purchase_items terbaru, dengan default periode 6 data terakhir
    public static function predictPrice(int $productId, int $period = 6): ?float
    {
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
