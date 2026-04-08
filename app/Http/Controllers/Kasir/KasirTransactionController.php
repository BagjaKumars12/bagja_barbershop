<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
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
            'customer_id' => 'required|exists:customers,id',
            'barber_id'   => 'required|exists:barbers,id',
            'services'    => 'required|array|min:1',
            'services.*.id' => 'exists:services,id',
            'services.*.quantity' => 'integer|min:1',
            'notes'       => 'nullable|string',
            'payment_method' => 'required|in:cash,card,transfer',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        // Hitung total harga
        $serviceIds = collect($request->services)->pluck('id')->toArray();
        $quantities = collect($request->services)->pluck('quantity', 'id')->toArray();
        $servicesData = Service::whereIn('id', $serviceIds)->get();
        $totalPrice = 0;
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

        // Simpan service dengan quantity
        foreach ($servicesData as $service) {
            $booking->services()->attach($service->id, ['quantity' => $quantities[$service->id]]);
        }

        // Buat transaksi
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'amount'     => $totalPrice,
            'payment_method' => $request->payment_method,
            'status'     => 'paid',
            'paid_at'    => Carbon::now(),
        ]);

        // Update customer total visits
        $customer = $booking->customer;
        $customer->total_visits = ($customer->total_visits ?? 0) + 1;
        $customer->last_visit = Carbon::now();
        $customer->save();

        return redirect()->route('kasir.transactions.receipt', $transaction->id)
            ->with('success', 'Transaksi berhasil!');
    }

    private function generateBookingCode()
    {
        $last = Booking::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->booking_code, 2)) + 1 : 1;
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
            'payment_method' => 'required|in:cash,card,transfer',
            'paid_amount' => 'required|numeric|min:' . $booking->total_price,
        ]);

        $total = $booking->total_price;
        $paid = $request->paid_amount;
        $change = $paid - $total;

        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'amount' => $total,
            'payment_method' => $request->payment_method,
            'status' => 'paid',
            'paid_at' => Carbon::now(),
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
}