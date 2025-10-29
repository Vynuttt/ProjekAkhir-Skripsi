<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Barang - Toko Sparepart Jaya Muncul</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h2, h4 { text-align: center; margin: 4px 0; }
        p { text-align: center; margin: 3px 0; font-size: 10.5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px; font-size: 10px; }
        th { background-color: #f4f4f4; text-align: center; }
        td { text-align: center; }
        .section-title { margin-top: 25px; font-size: 13px; font-weight: bold; text-align: left; }
        .footer { margin-top: 40px; text-align: right; width: 80%; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div style="text-align:center;">
        <h2>TOKO SPAREPART JAYA MUNCUL</h2>
        <p>Jl. KH. Abul Hasan No.9, Ps. Pagi, Samarinda</p>
        <p> </p>
        <p><strong>LAPORAN TRANSAKSI BARANG</strong></p>
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}</p>
        <hr style="border: 0; border-top: 1px solid #999; margin: 5px 0;">
    </div>

    {{-- Periode --}}
    <p>
        <strong>Periode:</strong>
        @if($filter === 'custom' && $start && $end)
            {{ \Carbon\Carbon::parse($start)->format('d M Y') }} – {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        @elseif($filter === 'daily')
            {{ now()->translatedFormat('d M Y') }}
        @elseif($filter === 'weekly')
            {{ $queryStart->translatedFormat('d M Y') }} – {{ $queryEnd->translatedFormat('d M Y') }}
        @elseif($filter === 'monthly')
            {{ now()->translatedFormat('F Y') }}
        @else
            Seluruh Periode
        @endif
    </p>

    {{-- Laporan Pembelian --}}
    <div class="section-title">Laporan Pembelian (Barang Masuk)</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Transaksi</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPembelian = 0; @endphp
            @forelse ($purchases as $item)
                @php $totalPembelian += $item->subtotal; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->purchase->purchase_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $item->purchase->invoice_number }}</td>
                    <td style="text-align:left;">{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data pembelian</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">Total Pembelian:</td>
                <td style="text-align:right;">Rp {{ number_format($totalPembelian, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Laporan Penjualan --}}
    <div class="section-title">Laporan Penjualan (Barang Keluar)</div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Transaksi</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPenjualan = 0; @endphp
            @forelse ($sales as $item)
                @php $totalPenjualan += $item->subtotal; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->sale->sale_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $item->sale->invoice_number }}</td>
                    <td style="text-align:left;">{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data penjualan</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">Total Penjualan:</td>
                <td style="text-align:right;">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
