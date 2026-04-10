<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            width: 300px;
            margin: 20px auto;
            background: #f5f5f5;
        }
        .receipt {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { color: #D4AF37; margin-bottom: 5px; }
        .divider { border-top: 1px dashed #aaa; margin: 12px 0; }
        .row { display: flex; justify-content: space-between; margin: 6px 0; }
        .total { font-weight: bold; font-size: 1.1em; margin-top: 8px; }
        .footer { text-align: center; margin-top: 20px; font-size: 11px; color: #666; }
        .button-group {
            text-align: center;
            margin-top: 25px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }
        .btn-print {
            background: #D4AF37;
            color: white;
        }
        .btn-print:hover { background: #b8942f; }
        .btn-close {
            background: #6c757d;
            color: white;
        }
        .btn-close:hover { background: #5a6268; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .receipt { box-shadow: none; padding: 10px; }
            .button-group { display: none; }
        }
    </style>
</head>
<body>
<div class="receipt">
    <div class="flex gap-2">
        <a href="{{ route('kasir.transactions.invoice', $transaction->id) }}" target="_blank" class="text-blue-600">📄 Invoice</a>
        <form action="{{ route('kasir.transactions.send.email', $transaction->id) }}" method="POST" class="inline">
            @csrf
            <input type="email" name="email" placeholder="Email customer" value="{{ $transaction->booking->customer->email ?? '' }}" class="text-sm px-2 py-1 border rounded">
            <button type="submit" class="text-green-600">✉️ Kirim Email</button>
        </form>
        <form action="{{ route('kasir.transactions.send.wa', $transaction->id) }}" method="POST" class="inline">
            @csrf
            <input type="text" name="phone" placeholder="No WhatsApp" value="{{ $transaction->booking->customer->phone ?? '' }}" class="text-sm px-2 py-1 border rounded">
            <button type="submit" class="text-green-600">📱 Kirim WA</button>
        </form>
    </div>
    <div class="header">
        <h2>Bagja Barbershop</h2>
        <p>JL Arief Rohman Haikin No. 45<br>Subang, Jawa Barat</p>
    </div>
    <div class="divider"></div>
    
    <div class="row"><span>No. Transaksi:</span><span>{{ $transaction->id }}</span></div>
    <div class="row"><span>Tanggal:</span><span>{{ $transaction->paid_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Kasir:</span><span>{{ Auth::user()->username }}</span></div>
    
    <div class="divider"></div>
    
    @foreach($transaction->booking->services as $service)
    <div class="row">
        <span>{{ $service->name }}</span>
        <span>Rp {{ number_format($service->price,0,',','.') }}</span>
    </div>
    @endforeach
    
    <div class="divider"></div>
    
    <div class="row"><span>Total item:</span><span>{{ $transaction->booking->services->count() }}</span></div>
    <div class="row total"><span>TOTAL:</span><span>Rp {{ number_format($transaction->amount,0,',','.') }}</span></div>
    <div class="row"><span>Tunai:</span><span>Rp {{ number_format($transaction->paid_amount,0,',','.') }}</span></div>
    <div class="row"><span>Kembalian:</span><span>Rp {{ number_format($transaction->change_amount,0,',','.') }}</span></div>
    
    <div class="divider"></div>
    <div class="footer">
        Terima kasih telah berkunjung!<br>
        ~ Senyum itu sedekah ~
    </div>
</div>

<div class="button-group">
    <button class="btn-print" onclick="window.print()">🖨️ Print Struk</button>
    <button class="btn-close" onclick="handleClose()">✖️ Tutup</button>
</div>

<script>
    function handleClose() {
        // If this window was opened by a previous page, close it.
        if (window.opener) {
            window.close();
        } else {
            // Otherwise redirect to transaction index
            window.location.href = "{{ route('kasir.transactions') }}";
        }
    }
</script>
</body>
</html>