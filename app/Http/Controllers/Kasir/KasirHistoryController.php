<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class KasirHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $transactions = Transaction::with(['booking.customer', 'booking.service'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('booking.customer', fn($cq) => $cq->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('booking.service', fn($sq) => $sq->where('name', 'LIKE', "%{$search}%"));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('kasir.history', compact('transactions', 'search'));
    }
}