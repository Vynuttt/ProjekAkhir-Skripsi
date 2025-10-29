<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Sale;

class StatsOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full'; // Lebar penuh baris atas

    protected function getCards(): array
    {
        return [
            Card::make('Total Produk', Product::count())
                ->description('Jumlah seluruh produk')
                ->color('primary'),

            Card::make('Total Supplier', Supplier::count())
                ->description('Jumlah supplier terdaftar')
                ->color('success'),

            Card::make('Total Kategori', Category::count())
                ->description('Kategori produk')
                ->color('warning'),

            Card::make('Total Pembelian', Purchase::count())
                ->description('Transaksi pembelian')
                ->color('info'),

            Card::make('Total Penjualan', Sale::count())
                ->description('Transaksi penjualan')
                ->color('danger'),
        ];
    }
}
