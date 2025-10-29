<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\SalesReturn;

// Laporan Stock Movement (Pembelian + Penjualan)

Route::get('/stockmovement/pdf', function () {
    $start  = request('start');
    $end    = request('end');
    $filter = request('filter', 'daily'); // default: harian

    // Default ke hari ini
    $queryStart = now()->startOfDay();
    $queryEnd   = now()->endOfDay();

    // Sesuaikan filter
    if ($filter === 'weekly') {
        $queryStart = now()->startOfWeek();
        $queryEnd   = now()->endOfWeek();
    } elseif ($filter === 'monthly') {
        $queryStart = now()->startOfMonth();
        $queryEnd   = now()->endOfMonth();
    } elseif ($filter === 'custom' && $start && $end) {
        try {
            $queryStart = Carbon::parse($start)->startOfDay();
            $queryEnd   = Carbon::parse($end)->endOfDay();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Format tanggal tidak valid.']);
        }
    }

    // Ambil data pembelian
    $purchases = PurchaseItem::with(['product', 'purchase'])
        ->whereHas('purchase', fn ($q) => $q->whereBetween('purchase_date', [$queryStart, $queryEnd]))
        ->get();

    // Ambil data penjualan
    $sales = SaleItem::with(['product', 'sale'])
        ->whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$queryStart, $queryEnd]))
        ->get();

    // Hitung total pembelian & penjualan
    $totalPurchases = $purchases->sum('subtotal');
    $totalSales     = $sales->sum('subtotal');

    // Load PDF dengan view blade
    $pdf = Pdf::loadView('reports.stock-movement', [
        'purchases'      => $purchases,
        'sales'          => $sales,
        'totalPurchases' => $totalPurchases,
        'totalSales'     => $totalSales,
        'filter'         => $filter,
        'start'          => $start,
        'end'            => $end,
        'queryStart'     => $queryStart,
        'queryEnd'       => $queryEnd,
    ])->setPaper('a4', 'portrait');

    $fileName = 'laporan_transaksi_' . $filter . '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->download($fileName);
})->name('stockmovement.pdf');

/*
|--------------------------------------------------------------------------
| Laporan Keuangan
|--------------------------------------------------------------------------
*/
Route::get('/laporan-keuangan/pdf', function () {
    $start  = request('start');
    $end    = request('end');
    $filter = request('filter', 'daily');

    $queryStart = now()->startOfDay();
    $queryEnd   = now()->endOfDay();

    if ($filter === 'weekly') {
        $queryStart = now()->startOfWeek();
        $queryEnd   = now()->endOfWeek();
    } elseif ($filter === 'monthly') {
        $queryStart = now()->startOfMonth();
        $queryEnd   = now()->endOfMonth();
    } elseif ($filter === 'custom' && $start && $end) {
        try {
            $queryStart = Carbon::parse($start)->startOfDay();
            $queryEnd   = Carbon::parse($end)->endOfDay();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Format tanggal tidak valid.']);
        }
    }

    $purchases = PurchaseItem::with(['product', 'purchase'])
        ->whereHas('purchase', fn ($q) => $q->whereBetween('purchase_date', [$queryStart, $queryEnd]))
        ->get();

    $sales = SaleItem::with(['product', 'sale'])
        ->whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$queryStart, $queryEnd]))
        ->get();

    $totalPurchases = $purchases->sum('subtotal');
    $totalSales     = $sales->sum('subtotal');
    $profit         = $totalSales - $totalPurchases;

    // Kirim ke Blade financial-report.blade.php
    $pdf = Pdf::loadView('reports.financial-report', [
        'purchases'      => $purchases,
        'sales'          => $sales,
        'totalPurchases' => $totalPurchases,
        'totalSales'     => $totalSales,
        'profit'         => $profit,
        'filter'         => $filter,
        'start'          => $start,
        'end'            => $end,
        'queryStart'     => $queryStart,
        'queryEnd'       => $queryEnd,
    ])->setPaper('a4', 'portrait');

    $fileName = 'laporan_keuangan_' . $filter . '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->download($fileName);
})->name('financial.pdf');

/*
|--------------------------------------------------------------------------
| Laporan EOQ
|--------------------------------------------------------------------------
*/

Route::get('/eoq-report/pdf', function () {
    $products = Product::with(['category', 'supplier'])->get();

    $pdf = Pdf::loadView('reports.eoq-report', [
        'products' => $products,
    ])->setPaper('a4', 'landscape');

    $fileName = 'laporan_eoq_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->download($fileName);
})->name('eoq-report.pdf');

// Laporan Retur Barang
Route::get('/return-report/pdf', function () {
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

    $purchaseReturns = \App\Models\PurchaseReturn::with(['purchase', 'product'])
        ->whereBetween('return_date', [$queryStart, $queryEnd])
        ->get();

    $saleReturns = \App\Models\SalesReturn::with(['sale', 'product'])
        ->whereBetween('return_date', [$queryStart, $queryEnd])
        ->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.return-report', [
        'purchaseReturns' => $purchaseReturns,
        'saleReturns' => $saleReturns,
        'filter' => $filter,
        'start' => $start,
        'end' => $end,
        'queryStart' => $queryStart,
        'queryEnd' => $queryEnd,
    ])->setPaper('a4', 'portrait');

    return $pdf->download('laporan_retur_' . now()->format('Ymd_His') . '.pdf');
})->name('return-report.pdf');
