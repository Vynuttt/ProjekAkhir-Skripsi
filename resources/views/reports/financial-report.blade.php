<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h2, h4 { text-align: center; margin: 4px 0; }
        table { width: 70%; margin: 20px auto; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 8px; }
        th { background: #f4f4f4; text-align: left; }
        td { text-align: right; }
        .no-border td { border: none; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; }
        .footer { margin-top: 40px; text-align: right; width: 80%; }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;

        $filterLabels = [
            'daily'   => 'Harian',
            'weekly'  => 'Mingguan',
            'monthly' => 'Bulanan',
            'yearly'  => 'Tahunan',
            'custom'  => 'Custom',
        ];

        if ($filter === 'monthly') {
            $periode = Carbon::parse($queryStart)->translatedFormat('F Y');
        } elseif ($filter === 'yearly') {
            $periode = Carbon::parse($queryStart)->translatedFormat('Y');
        } elseif ($filter === 'weekly') {
            $periode = Carbon::parse($queryStart)->format('d M Y') . ' - ' . Carbon::parse($queryEnd)->format('d M Y');
        } elseif ($filter === 'custom') {
            $periode = Carbon::parse($start)->format('d M Y') . ' - ' . Carbon::parse($end)->format('d M Y');
        } else {
            $periode = Carbon::parse($queryStart)->translatedFormat('d M Y');
        }

        $profit = $totalSales - $totalPurchases;
    @endphp

    {{-- Header Toko --}}
    <div class="header">
        <h2>TOKO SPAREPART JAYA MUNCUL</h2>
        <p>Jl. KH. Abul Hasan No.9, Ps. Pagi, Samarinda</p>
        <p><strong>Laporan Keuangan ({{ $filterLabels[$filter] ?? ucfirst($filter) }})</strong></p>
        <p>Periode: {{ $periode }}</p>
        <hr style="border: 0; border-top: 1px solid #999; margin-top: 8px;">
    </div>

    {{-- Tabel Ringkasan --}}
    <table>
        <tr>
            <th style="width: 50%">Total Penjualan</th>
            <td>Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Pembelian</th>
            <td>Rp {{ number_format($totalPurchases, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Laba / Rugi</th>
            <td style="color: {{ $profit >= 0 ? 'green' : 'red' }}">
                Rp {{ number_format($profit, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    {{-- Waktu Cetak --}}
    <p style="text-align: center; font-size: 10px; margin-top: 15px;">
        Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }}
    </p>

</body>
</html>
