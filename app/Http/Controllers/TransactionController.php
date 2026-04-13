<?php

namespace App\Http\Controllers;

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

class TransactionController extends Controller
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

        return view('admin.transactions.index', compact('queues', 'services', 'customers', 'barbers'));
    }

    // Tampilkan struk
    public function receipt($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        return view('admin.transactions.receipt', compact('transaction'));
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
        return view('admin.transactions.invoice', compact('transaction'));
    }

    // Generate PDF invoice
    public function generateInvoicePdf($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        $pdf = Pdf::loadView('admin.transactions.invoice_pdf', compact('transaction'));
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

        $pdf = Pdf::loadView('admin.transactions.invoice_pdf', compact('transaction'));

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
}