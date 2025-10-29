<?php

namespace App\Services;

use App\Models\PurchaseItem;

class PricePredictionService
{
    public static function predictPurchasePrice(int $productId, int $period = 6): ?float
    {
        // Ambil data harga pembelian terakhir dari tabel purchase_items
        $prices = PurchaseItem::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->take($period)
            ->pluck('price');

        // Jika belum ada data pembelian produk ini, kembalikan null
        if ($prices->count() === 0) {
            return null;
        }

        // Hitung rata-rata harga pembelian (rounded ke 2 desimal)
        return round($prices->avg(), 2);
    }
}
