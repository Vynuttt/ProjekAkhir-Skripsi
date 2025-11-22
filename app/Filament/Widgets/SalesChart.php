<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Purchase;
use App\Models\Sale;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pembelian & Penjualan Bulanan';
    protected int|string|array $columnSpan = 'full'; // Lebar penuh baris bawah

    protected function getData(): array
    {
        $purchases = Purchase::selectRaw('MONTH(purchase_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $sales = Sale::selectRaw('MONTH(sale_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        return [
            'datasets' => [
                [
                    'label' => 'Pembelian',
                    'data' => array_map(fn ($i) => $purchases[$i] ?? 0, range(1, 12)),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                ],
                [
                    'label' => 'Penjualan',
                    'data' => array_map(fn ($i) => $sales[$i] ?? 0, range(1, 12)),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // bisa diganti 'bar'
    }
}