<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Retur Barang</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2, h3 { text-align: center; margin: 5px 0; }
        p { text-align: center; margin: 4px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 10px; }
        th { background-color: #f2f2f2; }
        .section-title { margin-top: 25px; font-size: 13px; font-weight: bold; text-align: left; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
    </style>
</head>

<body>

    <h2>TOKO SPAREPART JAYA MUNCUL</h2>
    <p>Jl. KH. Abul Hasan No.9, Ps. Pagi, Samarinda</p>
    <h3>LAPORAN RETUR PEMBELIAN & PENJUALAN</h3>

    <p>Dicetak pada: {{ now()->setTimezone('Asia/Makassar')->translatedFormat('d F Y, H:i') }}</p>
    <hr style="border:0; border-top:1px solid #999; margin:5px 0;">

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

    {{-- ========================================= --}}
    {{-- RETUR PEMBELIAN --}}
    {{-- ========================================= --}}
    <div class="section-title">Retur Pembelian (Barang ke Supplier)</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Pembelian</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Total Retur</th>
                <th>Alasan</th>
            </tr>
        </thead>

        <tbody>
            @php $totalPurchaseReturn = 0; @endphp

            @forelse ($purchaseReturns as $item)
                @php $totalPurchaseReturn += $item->total; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->return_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $item->purchase->invoice_number }}</td>
                    <td style="text-align:left;">{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                    <td style="text-align:left;">{{ $item->reason ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada data retur pembelian</td></tr>
            @endforelse

            <tr class="total-row">
                <td colspan="5" style="text-align:right;">Total Retur Pembelian:</td>
                <td style="text-align:right;">Rp {{ number_format($totalPurchaseReturn, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- ========================================= --}}
    {{-- RETUR PENJUALAN --}}
    {{-- ========================================= --}}
    <div class="section-title">Retur Penjualan (Dari Pelanggan)</div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Penjualan</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Harga Satuan</th>
                <th>Total Retur</th>
                <th>Jenis</th>
                <th>Alasan</th>
            </tr>
        </thead>

        <tbody>
            @php $totalSalesRefund = 0; @endphp
            
            @forelse ($saleReturns as $item)
                @php
                    // Semua retur (refund & exchange) dihitung sebagai nilai retur
                    $totalSalesRefund += $item->total;
                @endphp
            
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->return_date)->translatedFormat('d M Y') }}</td>
                    <td>{{ $item->sale->invoice_number }}</td>
                    <td style="text-align:left;">{{ $item->product->name }}</td>
                    <td>{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td style="text-align:right;">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
            
                    {{-- Tampilkan total retur, baik refund maupun exchange --}}
                    <td style="text-align:right;">
                        Rp {{ number_format($item->total, 0, ',', '.') }}
                    </td>
            
                    <td>{{ $item->return_type === 'refund' ? 'Refund' : 'Exchange' }}</td>
                    <td style="text-align:left;">{{ $item->reason ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data retur penjualan</td></tr>
            @endforelse
            
            <tr class="total-row">
                <td colspan="5" style="text-align:right;">Total Retur Penjualan:</td>
                <td style="text-align:right;">Rp {{ number_format($totalSalesRefund, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>

        </tbody>
    </table>

</body>
</html>