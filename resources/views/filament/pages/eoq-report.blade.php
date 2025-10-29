<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold flex items-center gap-2">
                Laporan EOQ (Economic Order Quantity)
            </h2>
            <a href="{{ route('eoq-report.pdf') }}"
               class="bg-red-600 hover:bg-red-700 text-gray px-4 py-2 rounded-md shadow">
                Download PDF
            </a>
        </div>

        <!-- Kontainer tabel -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h3 class="text-lg font-bold mb-4 text-blue-400">
                Daftar Perhitungan EOQ (Economic Order Quantity)
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-700 text-white">
                        <tr>
                            <th class="p-2 border">Kode</th>
                            <th class="p-2 border">Produk</th>
                            <th class="p-2 border">Kategori</th>
                            <th class="p-2 border">Supplier</th>
                            <th class="p-2 border">Stok</th>
                            <th class="p-2 border">Demand / Tahun</th>
                            <th class="p-2 border">Biaya Pesan (S)</th>
                            <th class="p-2 border">Biaya Simpan (H)</th>
                            <th class="p-2 border">EOQ</th>
                            <th class="p-2 border">ROP</th>
                            <th class="p-2 border">Safety Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-b border-gray-600 odd:bg-gray-100 even:bg-white dark:odd:bg-gray-800 dark:even:bg-gray-900">
                                <td class="p-2 border text-center">{{ $product['code'] }}</td>
                                <td class="p-2 border text-left font-medium text-gray-900 dark:text-gray-100">
                                    {{ $product['name'] }}
                                </td>
                                <td class="p-2 border text-left">{{ $product['category'] ?? '-' }}</td>
                                <td class="p-2 border text-left">{{ $product['supplier'] ?? '-' }}</td>
                                <td class="p-2 border text-center">{{ number_format($product['stock'], 0, ',', '.') }}</td>
                                <td class="p-2 border text-center">{{ number_format($product['annual_demand'], 0, ',', '.') }}</td>
                                <td class="p-2 border text-right">Rp {{ number_format($product['ordering_cost'], 0, ',', '.') }}</td>
                                <td class="p-2 border text-right">Rp {{ number_format($product['holding_cost'], 0, ',', '.') }}</td>
                                <td class="p-2 border font-bold text-green-500 text-center">{{ $product['eoq'] }}</td>
                                <td class="p-2 border text-center">{{ $product['reorder_point'] }}</td>
                                <td class="p-2 border text-center">{{ $product['safety_stock'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data produk untuk ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <tfoot>
                            <tr class="bg-gray-700 text-white font-semibold">
                                <td colspan="6" class="p-2 text-right">Total Produk:</td>
                                <td colspan="5" class="p-2 text-left">{{ $products->total() }} item</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
