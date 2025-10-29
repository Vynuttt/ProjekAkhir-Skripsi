<?php

namespace App\Services;

use App\Models\SaleItem;

class PricePredictionService
{
    public static function predictPrice(int $productId, int $period = 6): ?float
    {
        $prices = SaleItem::where('product_id', $productId)
            ->orderByDesc('created_at')
            ->take($period)
            ->pluck('price');

        if ($prices->count() === 0) {
            return null;
        }

        return round($prices->avg(), 2);
    }
}
