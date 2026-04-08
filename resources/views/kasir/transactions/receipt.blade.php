<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        body { font-family: monospace; width: 300px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; }
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bagja Barbershop</h2>
        <p>JL Arief Rohman Haikin No. 45<br>Subang, Jawa Barat</p>
    </div>
    <div class="divider"></div>
    <p><strong>No. Transaksi:</strong> {{ $transaction->id }}</p>
    <p><strong>Tanggal:</strong> {{ $transaction->paid_at->format('d M Y, H:i') }}</p>
    <p><strong>Kasir:</strong> {{ Auth::user()->username }}</p>
    <div class="divider"></div>
    <table style="width:100%">
        @foreach($transaction->booking->services as $service)
        <tr>
            <td>{{ $service->name }}</td>
            <td style="text-align:right">Rp {{ number_format($service->price,0,',','.') }}</td>
        </tr>
        @endforeach
    </table>
    <div class="divider"></div>
    <p><strong>Total item:</strong> {{ $transaction->booking->services->count() }}</p>
    <p><strong>Total:</strong> Rp {{ number_format($transaction->amount,0,',','.') }}</p>
    <p><strong>Tunai:</strong> Rp {{ number_format(request()->input('paid', $transaction->amount),0,',','.') }}</p>
    <p><strong>Kembalian:</strong> Rp {{ number_format(request()->input('paid', 0) - $transaction->amount,0,',','.') }}</p>
    <div class="divider"></div>
    <div class="footer">
        Terimakasih telah berbelanja,<br>semoga harian anda menyenangkan!
    </div>
    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()">Print Struk</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</body>
</html>