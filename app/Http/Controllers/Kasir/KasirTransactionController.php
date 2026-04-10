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

    // Method untuk menyimpan transaksi cepat
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

        // Buat transaksi
        $transaction = Transaction::create([
            'booking_id'      => $booking->id,
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

    private function generateBookingCode()
    {
        $last = Booking::orderBy('id', 'desc')->first();
        $number = $last ? (int)substr($last->booking_code, 2) + 1 : 1;
        return 'BK' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // Ambil total booking via AJAX
    public function getBookingTotal($id)
    {
        $booking = Booking::findOrFail($id);
        return response()->json(['total' => $booking->total_price]);
    }

    // Proses pembayaran dari booking
    public function processPayment(Request $request, $bookingId)
    {
        $booking = Booking::with(['services', 'customer'])->findOrFail($bookingId);
        
        $request->validate([
            'payment_method' => 'required|in:cash,qris',
            'paid_amount' => 'required|numeric|min:' . $booking->total_price,
        ]);

        $total = $booking->total_price;
        $paid = $request->paid_amount;
        $change = $paid - $total;

        $transaction = Transaction::create([
            'booking_id'      => $booking->id,
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

    public function history(Request $request)
    {
        $query = Transaction::with(['booking.customer', 'booking.barber', 'booking.services'])
            ->where('status', 'paid')
            ->orderBy('paid_at', 'desc');

        // Pencarian berdasarkan customer atau kode booking
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

        // Filter tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        $transactions = $query->paginate(15)->withQueryString();

        return view('kasir.transactions.history', compact('transactions'));
    }

    // Menampilkan invoice di browser
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

        $pdf = Pdf::loadView('kasir.transactions.invoice_pdf', compact('transaction'));
        
        Mail::send([], [], function ($message) use ($email, $pdf, $transaction) {
            $message->to($email)
                    ->subject('Invoice Transaksi #' . $transaction->id)
                    ->text('Terima kasih telah menggunakan jasa kami. Berikut invoice transaksi Anda.')
                    ->attachData($pdf->output(), 'invoice_' . $transaction->id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
        });

        // Log jika diperlukan
        InvoiceSentLog::create([
            'transaction_id' => $transaction->id,
            'type' => 'email',
            'destination' => $email,
            'status' => true,
        ]);

        return back()->with('success', 'Invoice berhasil dikirim ke email ' . $email);
    }

    // Kirim invoice ke WhatsApp (menggunakan Fonnte API sebagai contoh)
    public function sendInvoiceWhatsapp(Request $request, $id)
    {
        $transaction = Transaction::with(['booking.customer'])->findOrFail($id);
        $phone = $request->phone ?? $transaction->booking->customer->phone;
        
        if (!$phone) {
            return back()->with('error', 'Nomor WhatsApp customer tidak tersedia.');
        }

        // Bersihkan nomor telepon (08123456789 -> 628123456789)
        $phone = preg_replace('/^0/', '62', $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Generate PDF invoice
        $pdf = Pdf::loadView('kasir.transactions.invoice_pdf', compact('transaction'));
        $pdfContent = $pdf->output();
        $pdfBase64 = base64_encode($pdfContent);
        
        // Kirim ke Fonnte API (contoh)
        $apiKey = env('FONNTE_API_KEY'); // set di .env
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'target' => $phone,
                'message' => "Halo, terima kasih telah berkunjung ke Bagja Barbershop. Berikut invoice transaksi Anda.",
                'filename' => 'invoice_' . $transaction->id . '.pdf',
                'file' => $pdfBase64,
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $apiKey
            ],
        ]);
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return back()->with('error', 'Gagal mengirim via WhatsApp: ' . $error);
        }
        
        InvoiceSentLog::create([
            'transaction_id' => $transaction->id,
            'type' => 'whatsapp',
            'destination' => $phone,
            'status' => true,
            'response' => $response,
        ]);
        
        return back()->with('success', 'Invoice berhasil dikirim ke WhatsApp ' . $phone);
    }
}