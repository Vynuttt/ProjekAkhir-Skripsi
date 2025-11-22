<x-filament-panels::page>
    <div class="space-y-6">

        {{-- FILTER PERIODE --}}
        <form method="GET" class="bg-gray-900 p-6 rounded-lg shadow space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-300 mb-1">Periode</label>
                    <select name="filter"
                        class="bg-gray-800 border border-gray-600 text-gray-200 rounded-lg px-3 py-2">
                        <option value="daily" {{ request('filter')=='daily'?'selected':'' }}>Harian</option>
                        <option value="weekly" {{ request('filter')=='weekly'?'selected':'' }}>Mingguan</option>
                        <option value="monthly" {{ request('filter')=='monthly'?'selected':'' }}>Bulanan</option>
                        <option value="custom" {{ request('filter')=='custom'?'selected':'' }}>Custom</option>
                    </select>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-300 mb-1">Dari</label>
                    <input type="date" name="start" value="{{ request('start') }}"
                        class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg pl-14 pr-3 py-2 w-full">
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-300 mb-1">Sampai</label>
                    <input type="date" name="end" value="{{ request('end') }}"
                        class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg pl-14 pr-3 py-2 w-full">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-gray-semibold px-5 py-2 rounded-lg shadow">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5" />
                    <span>Tampilkan</span>
                </button>

                <a href="{{ route('stockmovement.pdf', request()->all()) }}"
                    class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-gray-semibold px-5 py-2 rounded-lg shadow">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                    <span>Download PDF</span>
                </a>
            </div>
        </form>

        {{-- TABEL --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-gray-200">
                Daftar Transaksi Barang
            </h3>

            @php
                $filter = request('filter', 'daily');
                $start = request('start');
                $end = request('end');

                $queryStart = now()->startOfDay();
                $queryEnd = now()->endOfDay();

                if ($filter == 'weekly') {
                    $queryStart = now()->startOfWeek();
                    $queryEnd = now()->endOfWeek();
                } elseif ($filter == 'monthly') {
                    $queryStart = now()->startOfMonth();
                    $queryEnd = now()->endOfMonth();
                } elseif ($filter == 'custom' && $start && $end) {
                    $queryStart = \Carbon\Carbon::parse($start)->startOfDay();
                    $queryEnd = \Carbon\Carbon::parse($end)->endOfDay();
                }

                $purchases = \App\Models\PurchaseItem::with(['product', 'purchase'])
                    ->whereHas('purchase', fn($q) => $q->whereBetween('purchase_date', [$queryStart, $queryEnd]))
                    ->get()
                    ->map(function ($i) {
                        return [
                            'date' => $i->purchase->purchase_date,
                            'type' => 'Pembelian',
                            'product' => $i->product->name,
                            'qty' => $i->quantity,
                            'price' => $i->price,
                            'subtotal' => $i->subtotal,
                        ];
                    });

                $sales = \App\Models\SaleItem::with(['product', 'sale'])
                    ->whereHas('sale', fn($q) => $q->whereBetween('sale_date', [$queryStart, $queryEnd]))
                    ->get()
                    ->map(function ($i) {
                        return [
                            'date' => $i->sale->sale_date,
                            'type' => 'Penjualan',
                            'product' => $i->product->name,
                            'qty' => $i->quantity,
                            'price' => $i->price,
                            'subtotal' => $i->subtotal,
                        ];
                    });

                $movements = collect()->merge($purchases)->merge($sales)->sortBy('date');
            @endphp

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-700 text-gray-300">
                        <tr>
                            <th class="p-2 border">Tanggal</th>
                            <th class="p-2 border">Jenis Transaksi</th>
                            <th class="p-2 border">Produk</th>
                            <th class="p-2 border">Qty</th>
                            <th class="p-2 border">Harga</th>
                            <th class="p-2 border">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($movements as $m)
                            <tr class="odd:bg-gray-100 even:bg-white dark:odd:bg-gray-800 dark:even:bg-gray-900 border-b border-gray-600 text-gray-900 dark:text-gray-100">
                                <td class="p-2 border text-center">
                                    {{ \Carbon\Carbon::parse($m['date'])->translatedFormat('d M Y') }}
                                </td>
                                <td class="p-2 border text-blue-500 font-semibold text-center">
                                    {{ $m['type'] }}
                                </td>
                                <td class="p-2 border">{{ $m['product'] }}</td>
                                <td class="p-2 border text-center">{{ $m['qty'] }}</td>
                                <td class="p-2 border text-right">Rp {{ number_format($m['price'], 0, ',', '.') }}</td>
                                <td class="p-2 border text-right">Rp {{ number_format($m['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-gray-500">
                                    Tidak ada data transaksi untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .date-input::-webkit-calendar-picker-indicator {
            filter: brightness(0);
            cursor: pointer;
        }
    </style>
</x-filament-panels::page>