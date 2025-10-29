<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\LowStockProductsWidget;
use App\Filament\Widgets\SalesChart;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverview::class, // tampil di baris atas
            LowStockProductsWidget::class, // tampil di baris tengah
            SalesChart::class,    // tampil di baris bawah
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1, // default 1 kolom
            'md' => 1,      // jika medium screen ke atas jadi 2 kolom
            'lg' => 1,      // jika large screen ke atas jadi 3 kolom
        ];
    }
}
