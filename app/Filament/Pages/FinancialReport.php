<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class FinancialReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Keuangan';
    protected static ?string $slug = 'financial-report';

    protected static string $view = 'filament.pages.financial-report';

    public static function canAccess(): bool
    {
        // hanya owner & admin yang bisa lihat laporan keuangan
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']);
    }
}
