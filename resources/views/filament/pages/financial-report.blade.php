<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ========== FILTER ========== --}}
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-300">Periode</label>
                <select id="filter" name="filter"
                    class="bg-gray-800 border border-gray-600 text-gray-200 rounded-lg px-3 py-2">
                    <option value="daily" {{ request('filter')==='daily' ? 'selected':'' }}>Harian</option>
                    <option value="weekly" {{ request('filter')==='weekly' ? 'selected':'' }}>Mingguan</option>
                    <option value="monthly" {{ request('filter')==='monthly' ? 'selected':'' }}>Bulanan</option>
                    <option value="custom" {{ request('filter')==='custom' ? 'selected':'' }}>Custom</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300">Dari</label>
                <input type="date" name="start" value="{{ request('start') }}"
                    class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg px-3 py-2 w-full">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300">Sampai</label>
                <input type="date" name="end" value="{{ request('end') }}"
                    class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg px-3 py-2 w-full">
            </div>

            <div class="flex gap-2 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-grey px-4 py-2 rounded-md">
                    Tampilkan
                </button>

                <a href="{{ route('financial.pdf', request()->all()) }}"
                    class="bg-red-600 hover:bg-red-700 text-grey px-4 py-2 rounded-md">
                    Download PDF
                </a>
            </div>
        </form>

        {{-- ========== RINGKASAN KEUANGAN ========== --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-4">Ringkasan Keuangan</h3>

@php
    $start  = request('start');
    $end    = request('end');
    $filter = request('filter', 'daily');

    // Default
    $queryStart = now()->startOfDay();
    $queryEnd   = now()->endOfDay();

    if ($filter === 'weekly') {
        $queryStart = now()->startOfWeek();
        $queryEnd   = now()->endOfWeek();
    } elseif ($filter === 'monthly') {
        $queryStart = now()->startOfMonth();
        $queryEnd   = now()->endOfMonth();
    } elseif ($filter === 'custom' && $start && $end) {
        $queryStart = \Carbon\Carbon::parse($start)->startOfDay();
        $queryEnd   = \Carbon\Carbon::parse($end)->endOfDay();
    }

    /* ===========================
       PEMBELIAN
    ============================ */
    $purchases = App\Models\PurchaseItem::with('product','purchase')
        ->whereHas('purchase', fn($q)=>$q->whereBetween('purchase_date',[$queryStart,$queryEnd]))
        ->get();

    $grossPurchases = $purchases->sum('subtotal'); // Tidak dikurangi retur

    $purchaseReturns = App\Models\PurchaseReturn::whereBetween('return_date',[$queryStart,$queryEnd])->get();
    $totalPurchaseReturns = $purchaseReturns->sum('total');

    /* ===========================
       PENJUALAN
    ============================ */
    $sales = App\Models\SaleItem::with('product','sale')
        ->whereHas('sale', fn($q)=>$q->whereBetween('sale_date',[$queryStart,$queryEnd]))
        ->get();

    $grossSales = $sales->sum('subtotal');

    /* ===========================
       RETUR PENJUALAN (REFUND ONLY)
       — Exchange diperlakukan seperti refund
    ============================ */
    $salesReturns = App\Models\SalesReturn::with('product','sale')
        ->whereBetween('return_date',[$queryStart,$queryEnd])
        ->get();

    $totalSalesReturns = $salesReturns->sum(function($ret){
        return $ret->quantity * ($ret->unit_price ?? 0);
    });

    /* ===========================
       FINAL HITUNG
    ============================ */
    $totalPurchases = $grossPurchases;
    $totalSales     = $grossSales;

    $profit = ($grossSales - $totalSalesReturns) - $grossPurchases;
@endphp

            {{-- UI CARD --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">

                <div class="p-4 bg-blue-100 rounded-lg">
                    <h4 class="text-sm font-medium">Total Pembelian</h4>
                    <p class="text-xl font-bold text-blue-600">
                        Rp {{ number_format($totalPurchases,0,',','.') }}
                    </p>
                </div>

                <div class="p-4 bg-red-100 rounded-lg">
                    <h4 class="text-sm font-medium">Retur Pembelian</h4>
                    <p class="text-xl font-bold text-red-600">
                        - Rp {{ number_format($totalPurchaseReturns,0,',','.') }}
                    </p>
                </div>

                <div class="p-4 bg-green-100 rounded-lg">
                    <h4 class="text-sm font-medium">Total Penjualan</h4>
                    <p class="text-xl font-bold text-green-600">
                        Rp {{ number_format($totalSales,0,',','.') }}
                    </p>
                </div>

                <div class="p-4 bg-yellow-100 rounded-lg">
                    <h4 class="text-sm font-medium">Retur Penjualan</h4>
                    <p class="text-xl font-bold text-yellow-600">
                        - Rp {{ number_format($totalSalesReturns,0,',','.') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 p-4 bg-gray-100 rounded-lg text-center">
                <h4 class="text-sm font-medium">Laba / Rugi</h4>
                <p class="text-2xl font-bold {{ $profit>=0 ? 'text-green-600':'text-red-600' }}">
                    Rp {{ number_format($profit,0,',','.') }}
                </p>
            </div>

        </div>
    </div>
</x-filament-panels::page>