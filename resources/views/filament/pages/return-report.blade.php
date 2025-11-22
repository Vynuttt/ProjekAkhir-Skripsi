<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Form Filter --}}
        <form method="GET" class="flex flex-wrap gap-4 items-end">
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

            {{-- Filter Tanggal --}}
            <div>
                <label class="block text-sm font-medium text-gray-300">Dari</label>
                <input type="date" name="start" value="{{ request('start') }}"
                    class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg pl-14 pr-3 py-2 w-full focus:ring focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300">Sampai</label>
                <input type="date" name="end" value="{{ request('end') }}"
                    class="date-input bg-gray-800 border border-gray-600 text-gray-200 rounded-lg pl-14 pr-3 py-2 w-full focus:ring focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="flex gap-2 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-grey px-4 py-2 rounded-md">Tampilkan</button>
                <a href="{{ route('return-report.pdf', request()->all()) }}"
                    class="bg-red-600 hover:bg-red-700 text-grey px-4 py-2 rounded-md">Download PDF</a>
            </div>
        </form>

        @php
            $page = app(\App\Filament\Pages\ReturnReport::class);
            $data = $page->getData();
            extract($data);
        @endphp

        {{-- ========================= --}}
        {{-- RETUR PEMBELIAN --}}
        {{-- ========================= --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-blue-400">Retur Pembelian (Barang ke Supplier)</h3>

            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-700 textgrey-300">
                    <tr>
                        <th class="p-2 border">Tanggal</th>
                        <th class="p-2 border">No. Pembelian</th>
                        <th class="p-2 border">Produk</th>
                        <th class="p-2 border">Qty</th>
                        <th class="p-2 border">Harga Satuan</th>
                        <th class="p-2 border">Total</th>
                        <th class="p-2 border">Alasan</th>
                    </tr>
                </thead>
                <tbody>

                    @php $totalPurchaseReturn = 0; @endphp

                    @forelse ($purchaseReturns as $item)
                        @php $totalPurchaseReturn += $item->total; @endphp
                        <tr class="border-b border-gray-600">
                            <td class="p-2 border">{{ \Carbon\Carbon::parse($item->return_date)->format('d M Y') }}</td>
                            <td class="p-2 border">{{ $item->purchase->invoice_number }}</td>
                            <td class="p-2 border text-left">{{ $item->product->name }}</td>
                            <td class="p-2 border">{{ $item->quantity }}</td>
                            <td class="p-2 border text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="p-2 border text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td class="p-2 border text-left">{{ $item->reason ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-gray-400 py-3">Tidak ada data retur pembelian</td></tr>
                    @endforelse

                    <tr class="bg-gray-700 text-white font-bold">
                        <td colspan="5" class="p-2 text-right">Total Retur Pembelian</td>
                        <td class="p-2 text-right">Rp {{ number_format($totalPurchaseReturn, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- ========================= --}}
        {{-- RETUR PENJUALAN --}}
        {{-- ========================= --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-green-400">Retur Penjualan (Dari Pelanggan)</h3>

            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-700 text-grey-300">
                    <tr>
                        <th class="p-2 border">Tanggal</th>
                        <th class="p-2 border">No. Penjualan</th>
                        <th class="p-2 border">Produk</th>
                        <th class="p-2 border">Qty</th>
                        <th class="p-2 border">Harga Satuan</th>
                        <th class="p-2 border">Total</th>
                        <th class="p-2 border">Jenis Retur</th>
                        <th class="p-2 border">Alasan</th>
                    </tr>
                </thead>

                <tbody>

                    @php $totalSaleReturn = 0; @endphp

                    @forelse ($saleReturns as $item)
                        {{-- Hanya retur REFUND yang dihitung --}}
                        @php
                            if ($item->return_type === 'refund') {
                                $totalSaleReturn += $item->total;
                            }
                        @endphp

                        <tr class="border-b border-gray-600">
                            <td class="p-2 border">{{ \Carbon\Carbon::parse($item->return_date)->format('d M Y') }}</td>
                            <td class="p-2 border">{{ $item->sale->invoice_number }}</td>
                            <td class="p-2 border text-left">{{ $item->product->name }}</td>
                            <td class="p-2 border">{{ $item->quantity }}</td>
                            <td class="p-2 border text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="p-2 border text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>

                            <td class="p-2 border">
                                {{ $item->return_type === 'exchange' ? 'Tukar Barang' : 'Refund' }}
                            </td>

                            <td class="p-2 border text-left">{{ $item->reason ?? '-' }}</td>
                        </tr>

                    @empty
                        <tr><td colspan="8" class="text-center text-gray-400 py-3">Tidak ada data retur penjualan</td></tr>
                    @endforelse

                    <tr class="bg-gray-700 text-white font-bold">
                        <td colspan="6" class="p-2 text-right">Total Retur Penjualan (Refund)</td>
                        <td class="p-2 text-right">Rp {{ number_format($totalSaleReturn, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>

                </tbody>
            </table>

        </div>
    </div>

    {{-- Style kalender --}}
    <style>
        .date-input::-webkit-calendar-picker-indicator {
            filter: brightness(0);
            opacity: 0.9;
            cursor: pointer;
        }
        .date-input:hover::-webkit-calendar-picker-indicator {
            filter: brightness(0.2);
        }
    </style>

</x-filament-panels::page>