<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class StockMovement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static string $view = 'filament.pages.stock-movement';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Transaksi Barang';
}
