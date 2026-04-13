<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use App\Models\InvoiceSentLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OwnerTransactionController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $queues = Booking::with(['customer', 'barber', 'services'])
            ->whereDate('booking_time', $today)
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('booking_time', 'asc')
            ->get();

        $services = Service::where('is_active', true)->get();
        $customers = Customer::orderBy('name')->get();
        $barbers = Barber::where('is_active', true)->orderBy('name')->get();

        return view('owner.transactions.index', compact('queues', 'services', 'customers', 'barbers'));
    }

    // Tampilkan struk
    public function receipt($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        return view('owner.transactions.receipt', compact('transaction'));
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255|unique:customers,email',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'id'   => $customer->id,
            'name' => $customer->name,
        ])->header('Content-Type', 'application/json')
          ->header('X-Debug-Bar', 'false');
    }

    // Menampilkan invoice di browser
    public function invoice($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        return view('owner.transactions.invoice', compact('transaction'));
    }

    // Generate PDF invoice
    public function generateInvoicePdf($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        $pdf = Pdf::loadView('owner.transactions.invoice_pdf', compact('transaction'));
        return $pdf->download('invoice_' . $transaction->id . '.pdf');
    }

    // Kirim invoice ke email
    public function sendInvoiceEmail(Request $request, $id)
    {
        $transaction = Transaction::with(['booking.customer'])->findOrFail($id);
        $email = $request->email ?? $transaction->booking->customer->email;

        if (!$email) {
            return back()->with('error', 'Email customer tidak tersedia.');
        }

        $pdf = Pdf::loadView('owner.transactions.invoice_pdf', compact('transaction'));

        Mail::send([], [], function ($message) use ($email, $pdf, $transaction) {
            $message->to($email)
                    ->subject('Invoice Transaksi #' . $transaction->id)
                    ->text('Terima kasih telah menggunakan jasa kami. Berikut invoice transaksi Anda.')
                    ->attachData($pdf->output(), 'invoice_' . $transaction->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
        });

        InvoiceSentLog::create([
            'transaction_id' => $transaction->id,
            'type'           => 'email',
            'destination'    => $email,
            'status'         => true,
        ]);

        return back()->with('success', 'Invoice berhasil dikirim ke email ' . $email);
    }

    /**
     * Buka WhatsApp Web dengan pesan terisi otomatis.
     * PDF di-download ke browser, lalu kasir attach manual di WA Web.
     * Tidak butuh API key / Fonnte.
     */
    public function openWhatsappWeb($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services'])->findOrFail($id);
        $customer    = $transaction->booking->customer;

        // Normalisasi nomor
        $phone = preg_replace('/[^0-9]/', '', $customer->phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Susun pesan teks
        $serviceList = $transaction->booking->services
            ->map(fn($s) => '- ' . $s->name . ' (Rp ' . number_format($s->price, 0, ',', '.') . ')')
            ->join("\n");

        $tanggal     = $transaction->paid_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i');
        $total       = 'Rp ' . number_format($transaction->amount, 0, ',', '.');
        $tunai       = 'Rp ' . number_format($transaction->paid_amount, 0, ',', '.');
        $kembalian   = 'Rp ' . number_format($transaction->change_amount, 0, ',', '.');

        $message = "*Bagja Barbershop*\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "Halo, *{$customer->name}*! Terima kasih telah berkunjung \n\n"
                . "*Detail Transaksi #" . $transaction->id . "*\n"
                . "Tanggal : {$tanggal}\n\n"
                . "*Layanan:*\n{$serviceList}\n\n"
                . "Total     : *{$total}*\n"
                . "Tunai     : {$tunai}\n"
                . "Kembalian : {$kembalian}\n\n"
                . "━━━━━━━━━━━━━━━━━━━━\n"
                . "_Invoice terlampir_";

        // Encode untuk URL
        $waUrl = 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);

        return response()->json([
            'wa_url'      => $waUrl,
            'has_phone'   => (bool) $phone,
            'customer'    => $customer->name,
        ]);
    }

    /**
     * Kirim invoice ke WhatsApp via Fonnte API.
     *
     * Flow:
     *  1. Simpan PDF invoice ke storage/app/temp sementara.
     *  2. Kirim pesan teks berisi ringkasan transaksi + link struk.
     *  3. Kirim file PDF sebagai attachment (multipart/form-data).
     *  4. Hapus file temp setelah dikirim.
     *  5. Catat log ke InvoiceSentLog.
     *
     * Env yang dibutuhkan:
     *  FONNTE_API_KEY=<token_dari_dashboard_fonnte>
     *
     * Endpoint Fonnte:
     *  POST https://api.fonnte.com/send   → teks & file
     *  Header: Authorization: <api_key>
     */
    public function sendInvoiceWhatsapp(Request $request, $id)
    {
        // ── 1. Ambil data transaksi ──────────────────────────────────────────
        $transaction = Transaction::with(['booking.customer', 'booking.services'])->findOrFail($id);
        $customer    = $transaction->booking->customer;

        $phone = $request->phone ?? $customer->phone ?? null;

        if (!$phone) {
            return back()->with('error', 'Nomor WhatsApp customer tidak tersedia.');
        }

        // ── 2. Normalisasi nomor telepon ─────────────────────────────────────
        // Hilangkan semua karakter selain angka, lalu ubah awalan 0 → 62
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // ── 3. Validasi API Key ──────────────────────────────────────────────
        $apiKey = env('FONNTE_API_KEY');
        if (!$apiKey) {
            \Log::error('Fonnte: FONNTE_API_KEY tidak ditemukan di .env');
            return back()->with('error', 'Konfigurasi API WhatsApp belum diatur.');
        }

        // ── 4. Generate PDF dan simpan sementara ─────────────────────────────
        $pdf         = Pdf::loadView('owner.transactions.invoice_pdf', compact('transaction'));
        $pdfFilename = 'invoice_' . $transaction->id . '_' . time() . '.pdf';
        $pdfPath     = storage_path('app/temp/' . $pdfFilename);

        // Pastikan direktori temp ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $pdf->save($pdfPath);

        // ── 5. Susun pesan teks ───────────────────────────────────────────────
        $receiptUrl  = route('owner.transactions.receipt', $transaction->id);
        $totalFormat = 'Rp ' . number_format($transaction->amount, 0, ',', '.');
        $tanggal     = $transaction->paid_at
                        ? $transaction->paid_at->format('d/m/Y H:i')
                        : now()->format('d/m/Y H:i');

        $serviceList = $transaction->booking->services
            ->map(fn($s) => '  - ' . $s->name . ' (Rp ' . number_format($s->price, 0, ',', '.') . ')')
            ->join("\n");

        $message = "✂️ *Bagja Barbershop*\n"
                 . "━━━━━━━━━━━━━━━━━━━━\n"
                 . "Halo, *{$customer->name}*!\n"
                 . "Terima kasih telah berkunjung 🙏\n\n"
                 . "📋 *Detail Transaksi*\n"
                 . "No     : #{$transaction->id}\n"
                 . "Tanggal: {$tanggal}\n\n"
                 . "🪒 *Layanan:*\n{$serviceList}\n\n"
                 . "💰 *Total   : {$totalFormat}*\n"
                 . "💵 Tunai   : Rp " . number_format($transaction->paid_amount, 0, ',', '.') . "\n"
                 . "🔄 Kembalian: Rp " . number_format($transaction->change_amount, 0, ',', '.') . "\n\n"
                 . "🧾 Lihat struk: {$receiptUrl}\n"
                 . "━━━━━━━━━━━━━━━━━━━━\n"
                 . "_Invoice PDF terlampir_ 📎";

        // ── 6. Kirim pesan teks ───────────────────────────────────────────────
        $textResult = $this->fonnteRequest($apiKey, [
            'target'  => $phone,
            'message' => $message,
        ]);

        if (!$textResult['success']) {
            @unlink($pdfPath);
            return back()->with('error', 'Gagal mengirim pesan WA: ' . $textResult['error']);
        }

        // ── 7. Kirim PDF sebagai file attachment ──────────────────────────────
        $fileResult = $this->fonnteRequest($apiKey, [
            'target'  => $phone,
            'message' => 'Invoice #' . $transaction->id,
            'file'    => new \CURLFile($pdfPath, 'application/pdf', $pdfFilename),
        ]);

        // ── 8. Hapus file temp ────────────────────────────────────────────────
        @unlink($pdfPath);

        if (!$fileResult['success']) {
            // Pesan teks sudah terkirim, tapi PDF gagal → warning saja
            \Log::warning('Fonnte: Pesan terkirim tapi PDF gagal.', [
                'transaction_id' => $transaction->id,
                'phone'          => $phone,
                'error'          => $fileResult['error'],
            ]);

            InvoiceSentLog::create([
                'transaction_id' => $transaction->id,
                'type'           => 'whatsapp',
                'destination'    => $phone,
                'status'         => true, // Pesan teks tetap terkirim
                'note'           => 'Pesan terkirim, PDF gagal: ' . $fileResult['error'],
            ]);

            return back()->with('warning', 'Pesan terkirim, tapi PDF gagal dikirim: ' . $fileResult['error']);
        }

        // ── 9. Log sukses ─────────────────────────────────────────────────────
        InvoiceSentLog::create([
            'transaction_id' => $transaction->id,
            'type'           => 'whatsapp',
            'destination'    => $phone,
            'status'         => true,
        ]);

        return back()->with('success', 'Invoice berhasil dikirim ke WhatsApp ' . $phone);
    }

    /**
     * Helper: Kirim request ke Fonnte API via cURL.
     *
     * @param  string $apiKey  Token Fonnte
     * @param  array  $payload Data yang dikirim (bisa mengandung CURLFile untuk file)
     * @return array  ['success' => bool, 'error' => string|null, 'response' => array]
     */
    private function fonnteRequest(string $apiKey, array $payload): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Authorization: ' . $apiKey],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        \Log::info('Fonnte Request', [
            'http_code' => $httpCode,
            'response'  => $response,
            'error'     => $curlError,
            'target'    => $payload['target'] ?? null,
        ]);

        // cURL gagal (network error)
        if ($curlError) {
            return ['success' => false, 'error' => $curlError, 'response' => []];
        }

        // HTTP error
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "HTTP {$httpCode}", 'response' => []];
        }

        $data = json_decode($response, true) ?? [];

        // Fonnte mengembalikan status false
        if (isset($data['status']) && $data['status'] === false) {
            $reason = $data['reason'] ?? $data['message'] ?? 'Unknown error';
            return ['success' => false, 'error' => $reason, 'response' => $data];
        }

        return ['success' => true, 'error' => null, 'response' => $data];
    }
}