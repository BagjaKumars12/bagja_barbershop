<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KasirBookingController extends Controller
{
        // Generate booking code otomatis
    private function generateBookingCode()
    {
        $last = Booking::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->booking_code, 2)) + 1 : 1;
        return 'BK' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
    
    public function index(Request $request)
    {
        $search = $request->query('search');
        $bookings = Booking::with(['customer', 'barber', 'services'])
            ->when($search, function ($query, $search) {
                $query->where('booking_code', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('barber', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
            })
            ->orderBy('booking_time', 'desc')
            ->paginate(10)
            ->withQueryString();

        $todayCount = Booking::whereDate('booking_time', Carbon::today())->count();

        return view('kasir.bookings.index', compact('bookings', 'search', 'todayCount'));
    }

    public function create()
    {
        $customers = Customer::all();
        $services = Service::where('is_active', true)->get();
        $barbers = Barber::where('is_active', true)->get();

        return view('kasir.bookings.create', compact('customers', 'services', 'barbers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'barber_id'      => 'required|exists:barbers,id',
            'booking_time'   => 'required|date|after:now',
            'payment_method' => 'required|in:cash,card,transfer',
            'notes'          => 'nullable|string',
            'service_ids'    => 'required|array|min:1',      // array ID layanan
            'service_ids.*'  => 'exists:services,id'
        ]);

        $serviceIds = $validated['service_ids'];
        $services = Service::whereIn('id', $serviceIds)->get();
        $totalPrice = $services->sum('price');

        $booking = Booking::create([
            'booking_code'   => $this->generateBookingCode(),
            'customer_id'    => $validated['customer_id'],
            'barber_id'      => $validated['barber_id'],
            'booking_time'   => $validated['booking_time'],
            'status'         => 'pending',
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'],
            'total_price'    => $totalPrice,
        ]);

        // Simpan layanan ke pivot
        $booking->services()->attach($serviceIds);

        return redirect()->route('kasir.bookings.index')
            ->with('success', 'Booking berhasil ditambahkan.');
    }
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);
        $booking->status = $request->status;
        $booking->save();

        return redirect()->route('kasir.bookings.index')
            ->with('success', 'Status booking berhasil diubah.');
    }
}