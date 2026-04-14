<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function dashboard(Request $request)
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

        return view('owner.dashboard', compact(
            'todayTransactions', 'todayCustomers', 'activeUsers',
            'todayBookings', 'transactions', 'search', 'perPage'
        ));
    }

    public function users(Request $request)
    {
        $search = $request->query('search');

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where('username', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('owner.users.index', compact('users', 'search'));
    }

    private function validateUser(Request $request, $userId = null)
    {
        return $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'password' => $userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:admin,kasir,owner',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
            'is_active' => 'boolean', // tambahkan
        ]);
    }

    public function storeUser(Request $request)
    {
        $validated = $this->validateUser($request);
        
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = basename($avatarPath);
        }
        User::create([
            'username'  => $validated['username'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'avatar'    => $avatarPath,
            'is_active' => $request->boolean('is_active', true), // default true
        ]);
        return redirect()->route('owner.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $this->validateUser($request, $id);

        // Cegah update status jika user adalah owner (kecuali mungkin owner sendiri? tapi owner tidak punya akses ke halaman admin, jadi aman)
        if ($user->role === 'owner') {
            // Abaikan nilai is_active dari request, tetap gunakan nilai lama
            $request->request->set('is_active', $user->is_active);
        }

        // Cegah update status jika user yang diupdate adalah user yang sedang login (self update)
        if ($id == auth()->id()) {
            $request->request->set('is_active', true); // atau $user->is_active
        }

        // Proses avatar
        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            if ($avatarPath && Storage::disk('public')->exists('avatars/' . $avatarPath)) {
                Storage::disk('public')->delete('avatars/' . $avatarPath);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $avatarPath = basename($avatarPath);
        }

        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        if ($request->filled('password')) {
            $user->password = Hash::make($validated['password']);
        }
        $user->avatar = $avatarPath;
        $user->is_active = $request->boolean('is_active');
        $user->save();

        return redirect()->route('owner.users')->with('success', 'User berhasil diperbarui.');
    }
    
    public function destroyUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('owner.users')->with('success', 'User berhasil dihapus.');
    }
}