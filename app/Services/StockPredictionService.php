<?php

namespace App\Services;

use App\Models\Product;
use App\Services\DemandPredictionService;

class StockPredictionService
{
    /**
     * Prediksi stok barang pada periode tertentu (default 7 hari ke depan)
     *
     * @param int $productId ID produk
     * @param int $days Jumlah hari ke depan untuk prediksi
     * @return int|null Jumlah stok yang diperkirakan tersisa atau null jika tidak ada data
     */
    public static function predictStock(int $productId, int $days = 7): ?int
    {
        $product = Product::find($productId);

        if (!$product) {
            return null;
        }

        // Ambil hasil prediksi permintaan dari DemandPredictionService (SMA)
        $predictedDemand = DemandPredictionService::predictDemand($productId);

        if ($predictedDemand === null) {
            return null; // belum ada data penjualan
        }

        // Hitung stok prediksi (stok saat ini - permintaan yang diperkirakan)
        $predictedStock = $product->stock - ($predictedDemand * $days);

        // Pastikan hasil tidak negatif
        return max((int) round($predictedStock), 0);
    }
}
