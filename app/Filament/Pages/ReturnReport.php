<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\PurchaseReturn;
use App\Models\SalesReturn;
use Carbon\Carbon;

class ReturnReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Retur Barang';
    protected static ?string $slug = 'return-report';
    protected static string $view = 'filament.pages.return-report';

    public static function canAccess(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['owner', 'admin', 'kasir']);
    }

    public function getData()
    {
        $start = request('start');
        $end = request('end');
        $filter = request('filter', 'daily');

        $queryStart = now()->startOfDay();
        $queryEnd = now()->endOfDay();

        if ($filter === 'weekly') {
            $queryStart = now()->startOfWeek();
            $queryEnd = now()->endOfWeek();
        } elseif ($filter === 'monthly') {
            $queryStart = now()->startOfMonth();
            $queryEnd = now()->endOfMonth();
        } elseif ($filter === 'custom' && $start && $end) {
            $queryStart = Carbon::parse($start)->startOfDay();
            $queryEnd = Carbon::parse($end)->endOfDay();
        }

        $purchaseReturns = PurchaseReturn::with(['purchase', 'product'])
            ->whereBetween('return_date', [$queryStart, $queryEnd])
            ->get();

        $saleReturns = SalesReturn::with(['sale', 'product'])
            ->whereBetween('return_date', [$queryStart, $queryEnd])
            ->get();

        return compact('purchaseReturns', 'saleReturns', 'filter', 'start', 'end', 'queryStart', 'queryEnd');
    }
}
