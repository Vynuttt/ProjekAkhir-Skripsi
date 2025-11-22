<?php

namespace App\Services;

use App\Models\SaleItem;

class DemandPredictionService
{
    /**
     * Prediksi permintaan barang menggunakan Simple Moving Average (SMA)
     *
     * @param int $productId ID Produk
     * @param int $period Jumlah data penjualan terakhir yang digunakan
     * @return int|null Prediksi jumlah permintaan atau null jika tidak ada data
     */
    public static function predictDemand(int $productId, int $period = 6): ?int
    {
        $sales = SaleItem::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->take($period)
            ->pluck('quantity');

        if ($sales->count() === 0) {
            return null; 
        }

        return (int) round($sales->avg());
    }
}
