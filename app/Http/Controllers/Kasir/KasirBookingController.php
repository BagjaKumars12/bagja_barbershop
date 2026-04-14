<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Traits\BookingAvailability;

class KasirBookingController extends Controller
{
    use BookingAvailability;

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
            ->where('status', '!=', 'completed')
            ->when($search, function ($query, $search) {
                $query->where('booking_code', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
                    ->orWhereHas('barber', fn($q) => $q->where('name', 'LIKE', "%{$search}%"));
            })
            ->orderBy('booking_time', 'desc')
            ->paginate(10)
            ->withQueryString();

        $todayCount = Booking::whereDate('booking_time', Carbon::today())
        ->where('status', '!=', 'completed')
        ->count();

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
            'payment_method' => 'required|in:cash',
            'notes'          => 'nullable|string',
            'service_ids'    => 'required|array|min:1',
            'service_ids.*'  => 'exists:services,id'
        ]);

        // Jam operasional
        if (!$this->isWithinOperatingHours($validated['booking_time'])) {
            return back()->withErrors(['booking_time' => 'Jam operasional hanya 09:00 - 21:00.'])->withInput();
        }

        // Cek ketersediaan barber
        if (!$this->isBarberAvailable($validated['barber_id'], $validated['booking_time'])) {
            return back()->withErrors(['barber_id' => 'Barber sudah memiliki booking pada waktu yang hampir bersamaan. Pilih waktu atau barber lain.'])->withInput();
        }

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