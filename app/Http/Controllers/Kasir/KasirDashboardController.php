<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KasirDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $search = $request->query('search');
        $perPage = $request->query('per_page', 5);

        $todayTransactions = Transaction::whereDate('paid_at', $today)
            ->where('status', 'paid')->count();

        $todayCustomers = Booking::whereHas('transaction', function ($q) use ($today) {
            $q->whereDate('paid_at', $today)->where('status', 'paid');
        })->distinct('customer_id')->count('customer_id');

        $activeUsers = User::where('last_login_at', '>=', Carbon::now()->subHours(24))->count();
        $todayBookings = Booking::whereDate('booking_time', Carbon::today())
        ->where('status', '!=', 'completed')
        ->count();

        $query = Transaction::with(['booking.customer', 'booking.services'])
            ->whereDate('paid_at', $today)
            ->where('status', 'paid');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('booking.customer', fn($cq) => $cq->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('booking.services', fn($sq) => $sq->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $transactions = $query->orderBy('paid_at', 'desc')->paginate($perPage);

        return view('kasir.dashboard', compact(
            'todayTransactions', 'todayCustomers', 'activeUsers',
            'todayBookings', 'transactions', 'search', 'perPage'
        ));
    }
}