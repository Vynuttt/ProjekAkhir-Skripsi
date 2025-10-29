<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan EOQ - Toko Sparepart Jaya Muncul</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h2, h4 { text-align: center; margin: 4px 0; }
        table { width: 100%; margin: 15px auto; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; font-size: 10.5px; }
        th { background: #f4f4f4; text-align: center; }
        td { text-align: center; }
        .header { text-align: center; margin-bottom: 10px; }
        .footer { margin-top: 40px; text-align: right; width: 80%; }
    </style>
</head>
<body>
    {{-- Header Laporan --}}
    <div class="header">
        <h2>TOKO SPAREPART JAYA MUNCUL</h2>
        <p>Jl. KH. Abul Hasan No.9, Ps. Pagi, Samarinda</p>
        <p><strong>LAPORAN EOQ (Economic Order Quantity)</strong></p>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>
        <hr style="border: 0; border-top: 1px solid #999; margin-top: 6px;">
    </div>

    {{-- Tabel EOQ --}}
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Supplier</th>
                <th>Stok Saat Ini</th>
                <th>Permintaan / Tahun (D)</th>
                <th>Biaya Pemesanan (S)</th>
                <th>Biaya Penyimpanan (H)</th>
                <th>EOQ (Unit)</th>
                <th>Reorder Point (ROP)</th>
                <th>Safety Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->code }}</td>
                    <td style="text-align:left;">{{ $product->name }}</td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td>{{ $product->supplier?->name ?? '-' }}</td>
                    <td>{{ number_format($product->stock, 0, ',', '.') }}</td>
                    <td>{{ number_format($product->annual_demand, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($product->ordering_cost, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($product->holding_cost, 0, ',', '.') }}</td>
                    <td><strong>{{ number_format($product->calculateEOQ(), 0, ',', '.') }}</strong></td>
                    <td>{{ number_format($product->reorder_point, 0, ',', '.') }}</td>
                    <td>{{ number_format($product->safety_stock ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align:center;">Tidak ada data produk yang tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
