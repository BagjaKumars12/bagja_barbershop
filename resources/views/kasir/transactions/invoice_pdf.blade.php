<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $transaction->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #D4AF37;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #D4AF37;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #555;
        }
        .info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .info .left, .info .right {
            width: 48%;
        }
        .info .left strong, .info .right strong {
            display: block;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .thankyou {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #D4AF37;
        }
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .invoice-box { box-shadow: none; padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>BAGJA BARBERSHOP</h1>
            <p>JL Arief Rohman Haikin No. 45, Subang, Jawa Barat</p>
            <p>Telp: 0852-1234-5678 | Email: bagjabarbershop@gmail.com</p>
        </div>

        <div class="info">
            <div class="left">
                <strong>INVOICE</strong>
                <span>No: #{{ $transaction->id }}</span><br>
                <span>Tanggal: {{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : '-' }}</span><br>
                <span>Kasir: {{ auth()->user()->name ?? 'Admin' }}</span>
            </div>
            <div class="right">
                <strong>Pelanggan</strong>
                <span>{{ $transaction->booking->customer->name ?? '-' }}</span><br>
                <span>Email: {{ $transaction->booking->customer->email ?? '-' }}</span><br>
                <span>Telp: {{ $transaction->booking->customer->phone ?? '-' }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr><th>Layanan</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($transaction->booking->services as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->pivot->quantity ?? 1 }}</td>
                    <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($service->price * ($service->pivot->quantity ?? 1), 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align:right"><strong>Total</strong></td>
                    <td><strong>Rp {{ number_format($transaction->amount, 0, ',', '.') }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right">Tunai</td>
                    <td>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right">Kembalian</td>
                    <td>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="thankyou">
            Terima kasih telah berkunjung!<br>
            Senyum itu sedekah 😊
        </div>
        <div class="footer">
            Invoice ini dibuat secara elektronik dan tidak memerlukan tanda tangan.
        </div>
    </div>
</body>
</html>