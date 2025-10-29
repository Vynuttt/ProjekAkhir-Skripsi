<x-filament-panels::page>
    <div class="space-y-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
    <!-- Periode -->
    <div>
        <label for="filter" class="block text-sm font-medium text-gray-300">Periode</label>
        <select id="filter" name="filter"
            class="bg-gray-800 border border-gray-600 text-gray-200 rounded-lg px-3 py-2 focus:ring focus:ring-primary-500 focus:border-primary-500">
            <option value="daily" {{ request('filter') === 'daily' ? 'selected' : '' }}>Harian</option>
            <option value="weekly" {{ request('filter') === 'weekly' ? 'selected' : '' }}>Mingguan</option>
            <option value="monthly" {{ request('filter') === 'monthly' ? 'selected' : '' }}>Bulanan</option>
            <option value="custom" {{ request('filter') === 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
    </div>

<!-- Dari -->
<div>
    <label class="block text-sm font-medium text-gray-300">Dari</label>
    <div class="relative">
        <input type="date" name="start" value="{{ request('start') }}"
            class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg pl-14 pr-3 py-2 w-full focus:ring focus:ring-primary-500 focus:border-primary-500">
    </div>
</div>

<!-- Sampai -->
<div>
    <label class="block text-sm font-medium text-gray-300">Sampai</label>
    <div class="relative">
        <input type="date" name="end" value="{{ request('end') }}"
            class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg pl-14 pr-3 py-2 w-full focus:ring focus:ring-primary-500 focus:border-primary-500">
    </div>
</div>



    <!-- Tombol -->
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


        {{-- Ringkasan Keuangan --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-4">Ringkasan Keuangan</h3>
            @php
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
                    $queryStart = \Carbon\Carbon::parse($start)->startOfDay();
                    $queryEnd = \Carbon\Carbon::parse($end)->endOfDay();
                }

                $purchases = \App\Models\PurchaseItem::with('product','purchase')
                    ->whereHas('purchase', fn($q) => $q->whereBetween('purchase_date', [$queryStart, $queryEnd]))
                    ->get();

                $sales = \App\Models\SaleItem::with('product','sale')
                    ->whereHas('sale', fn($q) => $q->whereBetween('sale_date', [$queryStart, $queryEnd]))
                    ->get();

                $totalPurchases = $purchases->sum('subtotal');
                $totalSales = $sales->sum('subtotal');
                $profit = $totalSales - $totalPurchases;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                <div class="p-4 bg-blue-100 rounded-lg">
                    <h4 class="text-sm font-medium">Total Pembelian</h4>
                    <p class="text-xl font-bold text-blue-600">Rp {{ number_format($totalPurchases,0,',','.') }}</p>
                </div>
                <div class="p-4 bg-green-100 rounded-lg">
                    <h4 class="text-sm font-medium">Total Penjualan</h4>
                    <p class="text-xl font-bold text-green-600">Rp {{ number_format($totalSales,0,',','.') }}</p>
                </div>
                <div class="p-4 bg-yellow-100 rounded-lg">
                    <h4 class="text-sm font-medium">Laba / Rugi</h4>
                    <p class="text-xl font-bold {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format($profit,0,',','.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
/* Warna ikon kalender selalu hitam di semua mode */
.date-input::-webkit-calendar-picker-indicator {
    filter: brightness(0) saturate(100%) invert(0%) sepia(0%) saturate(100%) hue-rotate(0deg);
    opacity: 0.9;
    cursor: pointer;
}

/* Hover biar sedikit lebih gelap */
.date-input:hover::-webkit-calendar-picker-indicator {
    filter: brightness(0) saturate(100%) invert(0%) sepia(0%) saturate(200%) hue-rotate(0deg) brightness(80%);
}
</style>

</x-filament-panels::page>
