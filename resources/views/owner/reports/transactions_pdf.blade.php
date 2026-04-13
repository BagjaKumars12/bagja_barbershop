<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #D4AF37; }
        .header p { margin: 5px 0; }
        .filter-info { margin-bottom: 15px; font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bagja Barbershop</h1>
        <p>JL Arief Rohman Haikin No. 45, Subang, Jawa Barat</p>
        <h3>Laporan Transaksi</h3>
        <p>Periode: 
            @if(request('start_date') && request('end_date'))
                {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }} - {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
            @elseif(request('start_date'))
                Dari {{ \Carbon\Carbon::parse(request('start_date'))->format('d/m/Y') }}
            @elseif(request('end_date'))
                Sampai {{ \Carbon\Carbon::parse(request('end_date'))->format('d/m/Y') }}
            @else
                Semua waktu
            @endif
        </p>
    </div>

    <div class="filter-info">
        <strong>Total Transaksi:</strong> {{ $totalTransactions }} &nbsp;|&nbsp;
        <strong>Total Pendapatan:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }} &nbsp;|&nbsp;
        <strong>Rata-rata:</strong> Rp {{ number_format($totalTransactions > 0 ? $totalAmount / $totalTransactions : 0, 0, ',', '.') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode Transaksi</th>
                <th>Kode Booking</th>
                <th>Customer</th>
                <th>Barber</th>
                <th>Layanan</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->transaction_code }}</td>
                <td>{{ $transaction->booking->booking_code ?? '-' }}</td>
                <td>{{ $transaction->booking->customer->name ?? '-' }}</td>
                <td>{{ $transaction->booking->barber->name ?? '-' }}</td>
                <td>{{ $transaction->booking->services->pluck('name')->implode(', ') ?: '-' }}</td>
                <td>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                <td>{{ ucfirst($transaction->payment_method) }}</td>
                <td>{{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>