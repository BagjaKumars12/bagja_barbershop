<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use App\Models\InvoiceSentLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;
use App\Helpers\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KasirTransactionController extends Controller
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

        return view('kasir.transactions.index', compact('queues', 'services', 'customers', 'barbers'));
    }

    // Generate kode booking (BK...)
    private function generateBookingCode()
    {
        $last = Booking::orderBy('id', 'desc')->first();
        $number = $last ? (int)substr($last->booking_code, 2) + 1 : 1;
        return 'BK' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // Generate kode transaksi (TRX...)
    private function generateTransactionCode()
    {
        $last = Transaction::orderBy('id', 'desc')->first();
        if (!$last || !$last->transaction_code) {
            $number = 1;
        } else {
            $lastCode = $last->transaction_code;
            $number = (int) substr($lastCode, 3) + 1;
        }
        return 'TRX' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    // Method untuk menyimpan transaksi cepat (form langsung)
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'barber_id'      => 'required|exists:barbers,id',
            'services_json'  => 'required|json',
            'notes'          => 'nullable|string',
            'payment_method' => 'required|in:cash,qris',
            'paid_amount'    => 'required|numeric|min:0',
        ]);

        // Decode JSON services
        $services = json_decode($request->services_json, true);
        if (empty($services)) {
            return back()->withErrors(['services_json' => 'Pilih minimal satu layanan.']);
        }

        // Hitung total harga
        $totalPrice = 0;
        $serviceIds = [];
        $quantities = [];
        foreach ($services as $item) {
            $serviceIds[] = $item['id'];
            $quantities[$item['id']] = $item['quantity'];
        }

        $servicesData = Service::whereIn('id', $serviceIds)->get();
        foreach ($servicesData as $service) {
            $totalPrice += $service->price * $quantities[$service->id];
        }

        // Validasi jumlah bayar
        if ($request->paid_amount < $totalPrice) {
            return back()->withErrors(['paid_amount' => 'Jumlah bayar kurang dari total.']);
        }

        // Buat booking
        $booking = Booking::create([
            'booking_code'   => $this->generateBookingCode(),
            'customer_id'    => $request->customer_id,
            'barber_id'      => $request->barber_id,
            'booking_time'   => Carbon::now(),
            'status'         => 'completed',
            'payment_method' => $request->payment_method,
            'notes'          => $request->notes,
            'total_price'    => $totalPrice,
        ]);

        // Attach services dengan quantity
        foreach ($servicesData as $service) {
            $booking->services()->attach($service->id, ['quantity' => $quantities[$service->id]]);
        }

        $change = $request->paid_amount - $totalPrice;

        // Generate kode transaksi
        $transactionCode = $this->generateTransactionCode();

        // Buat transaksi (transaction_code diisi langsung)
        $transaction = Transaction::create([
            'booking_id'      => $booking->id,
            'transaction_code'=> $transactionCode,
            'amount'          => $totalPrice,
            'paid_amount'     => $request->paid_amount,
            'change_amount'   => $change,
            'payment_method'  => $request->payment_method,
            'status'          => 'paid',
            'paid_at'         => Carbon::now(),
        ]);

        // Update customer
        $customer = $booking->customer;
        $customer->total_visits += 1;
        $customer->last_visit = Carbon::now();
        $customer->save();

        return redirect()->route('kasir.transactions.receipt', $transaction->id)
                        ->with('success', 'Transaksi berhasil!');
    }

    // Ambil total booking via AJAX
    public function getBookingTotal($id)
    {
        $booking = Booking::findOrFail($id);
        return response()->json(['total' => $booking->total_price]);
    }

    // Proses pembayaran dari antrian (booking yang sudah ada)
    public function processPayment(Request $request, $bookingId)
    {
        $booking = Booking::with(['services', 'customer'])->findOrFail($bookingId);
        
        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'paid_amount'    => 'required|numeric|min:' . $booking->total_price,
        ]);

        $total  = $booking->total_price;
        $paid   = $request->paid_amount;
        $change = $paid - $total;

        // Generate kode transaksi
        $transactionCode = $this->generateTransactionCode();

        $transaction = Transaction::create([
            'booking_id'      => $booking->id,
            'transaction_code'=> $transactionCode,
            'amount'          => $total,
            'paid_amount'     => $paid,
            'change_amount'   => $change,
            'payment_method'  => $request->payment_method,
            'status'          => 'paid',
            'paid_at'         => Carbon::now(),
        ]);

        $booking->status = 'completed';
        $booking->save();

        $customer = $booking->customer;
        $customer->total_visits = ($customer->total_visits ?? 0) + 1;
        $customer->last_visit = Carbon::now();
        $customer->save();

        return redirect()->route('kasir.transactions.receipt', $transaction->id)
                         ->with('success', 'Pembayaran berhasil!');
    }

    // Tampilkan struk
    public function receipt($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        return view('kasir.transactions.receipt', compact('transaction'));
    }

    // Simpan customer cepat (AJAX)
    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return response()->json([
            'id'   => $customer->id,
            'name' => $customer->name,
        ])->header('Content-Type', 'application/json')
          ->header('X-Debug-Bar', 'false');
    }

    // Riwayat transaksi
    public function history(Request $request)
    {
        $query = Transaction::with(['booking.customer', 'booking.barber', 'booking.services'])
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('booking.customer', function ($cq) use ($search) {
                    $cq->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('booking', function ($bq) use ($search) {
                    $bq->where('booking_code', 'LIKE', "%{$search}%");
                });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(15)->withQueryString();
        return view('kasir.transactions.history', compact('transactions'));
    }

    // Tampilkan invoice di browser
    public function invoice($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        return view('kasir.transactions.invoice', compact('transaction'));
    }

    // Generate PDF invoice
    public function generateInvoicePdf($id)
    {
        $transaction = Transaction::with(['booking.customer', 'booking.services', 'booking.barber'])->findOrFail($id);
        $pdf = Pdf::loadView('kasir.transactions.invoice_pdf', compact('transaction'));
        return $pdf->download('invoice_' . $transaction->transaction_code . '.pdf');
    }

    // Kirim invoice ke email
    public function sendInvoiceEmail(Request $request, $id)
    {
        $transaction = Transaction::with(['booking.customer'])->findOrFail($id);
        $email = $request->email ?? $transaction->booking->customer->email;

        if (!$email) {
            return back()->with('error', 'Email customer tidak tersedia.');
        }

        $pdf = Pdf::loadView('kasir.transactions.invoice_pdf', compact('transaction'));

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