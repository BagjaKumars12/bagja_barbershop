<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Barber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Traits\BookingAvailability;

class BookingController extends Controller
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

        return view('admin.bookings.index', compact('bookings', 'search', 'todayCount'));
    }

    public function create()
    {
        $customers = Customer::all();
        $services = Service::where('is_active', true)->get();
        $barbers = Barber::where('is_active', true)->get();

        return view('admin.bookings.create', compact('customers', 'services', 'barbers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'barber_id'      => 'required|exists:barbers,id',
            'booking_time'   => 'required|date|after:now',
            'payment_method' => 'required|in:cash,card,transfer',
            'notes'          => 'nullable|string',
            'service_ids'    => 'required|array|min:1',
            'service_ids.*'  => 'exists:services,id'
        ]);

        // Validasi jam operasional
        if (!$this->isWithinOperatingHours($validated['booking_time'])) {
            return back()->withErrors(['booking_time' => 'Jam operasional hanya 09:00 - 21:00.'])->withInput();
        }

        // Validasi ketersediaan barber
        if (!$this->isBarberAvailable($validated['barber_id'], $validated['booking_time'])) {
            return back()->withErrors(['barber_id' => 'Barber sudah memiliki booking pada waktu yang hampir bersamaan. Pilih waktu atau barber lain.'])->withInput();
        }

        // Hitung total
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

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $booking = Booking::with(['customer', 'barber', 'services'])->findOrFail($id);
        $customers = Customer::all();
        $services = Service::all();
        $barbers = Barber::all();

        return view('admin.bookings.edit', compact('booking', 'customers', 'services', 'barbers'));
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'barber_id'      => 'required|exists:barbers,id',
            'booking_time'   => 'required|date',
            'status'         => 'required|in:pending,confirmed,completed,cancelled',
            'payment_method' => 'required|in:cash,card,transfer',
            'notes'          => 'nullable|string',
            'service_ids'    => 'required|array|min:1',
            'service_ids.*'  => 'exists:services,id'
        ]);

        // Validasi jam operasional (kecuali jika status sudah selesai/dibatalkan)
        if (in_array($validated['status'], ['pending', 'confirmed'])) {
            if (!$this->isWithinOperatingHours($validated['booking_time'])) {
                return back()->withErrors(['booking_time' => 'Jam operasional hanya 09:00 - 21:00.'])->withInput();
            }

            // Cek ketersediaan barber, kecualikan booking ini sendiri
            if (!$this->isBarberAvailable($validated['barber_id'], $validated['booking_time'], $id)) {
                return back()->withErrors(['barber_id' => 'Barber sudah memiliki booking pada waktu yang hampir bersamaan. Pilih waktu atau barber lain.'])->withInput();
            }
        }

        $services = Service::whereIn('id', $validated['service_ids'])->get();
        $totalPrice = $services->sum('price');

        $booking->update([
            'customer_id'    => $validated['customer_id'],
            'barber_id'      => $validated['barber_id'],
            'booking_time'   => $validated['booking_time'],
            'status'         => $validated['status'],
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'],
            'total_price'    => $totalPrice,
        ]);

        $booking->services()->sync($validated['service_ids']);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Booking berhasil dihapus.');
    }
}