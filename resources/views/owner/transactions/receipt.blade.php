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
        .action-buttons {
            text-align: center;
            margin-top: 20px;
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
        .btn-email {
            background: #28a745;
            color: white;
        }
        .btn-email:hover { background: #218838; }
        .btn-wa {
            background: #25D366;
            color: white;
        }
        .btn-wa:hover { background: #128C7E; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .receipt { box-shadow: none; padding: 10px; }
            .button-group { display: none; }
            .action-buttons { display: none; } /* Sembunyikan tombol kirim saat print */
        }
    </style>
</head>

<body>
    {{-- Tombol Aksi (Print & Kirim) --}}
    <div class="action-buttons">
        <form action="{{ route('owner.transactions.send.email', $transaction->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn-email">✉️ Kirim Email</button>
        </form>
        <button type="button" class="btn-wa" onclick="kirimViaWAWeb({{ $transaction->id }})">📱 Kirim WA</button>
    </div>
<div class="receipt">
    <div class="header">
        <h2>Bagja Barbershop</h2>
        <p>JL Arief Rohman Haikin No. 45<br>Subang, Jawa Barat</p>
    </div>
    <div class="divider"></div>
    
    <div class="row"><span>No. Transaksi:</span><span>{{ $transaction->id }}</span></div>
    <div class="row"><span>Tanggal:</span><span>{{ $transaction->paid_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Kasir:</span><span>{{ auth()->user()->username ?? 'Admin' }}</span></div>
    
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
        if (window.opener) {
            window.close();
        } else {
            window.location.href = "{{ route('owner.reports.transactions') }}";
        }
    }

    async function kirimViaWAWeb(transactionId) {
        const btn = event.target;
        btn.disabled = true;
        btn.textContent = '⏳ Menyiapkan...';

        try {
            const res  = await fetch(`/owner/transactions/${transactionId}/whatsapp-web`);
            const data = await res.json();

            if (!data.has_phone) {
                alert('Nomor WhatsApp customer tidak tersedia.');
                return;
            }

            // Langsung buka WA Web, pesan sudah terisi
            window.open(data.wa_url, '_blank');

        } catch (e) {
            alert('Terjadi kesalahan. Coba lagi.');
        } finally {
            btn.disabled = false;
            btn.textContent = '📱 Kirim WA';
        }
    }
</script>
</body>
</html>