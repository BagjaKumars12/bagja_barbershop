<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OwnerBookingController extends Controller
{
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

        return view('owner.bookings.index', compact('bookings', 'search', 'todayCount'));
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);
        $booking->status = $request->status;
        $booking->save();

        return redirect()->route('owner.bookings.index')
            ->with('success', 'Status booking berhasil diubah.');
    }
}