<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // Jika user tidak ditemukan
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        // Jika user tidak aktif
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ]);
        }

        $remember = $request->has('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $user->last_login_at = Carbon::now();
            $user->save();

            // Redirect berdasarkan role (gunakan 'username' karena kolom di DB adalah username)
            return match($user->role) {
                'admin' => redirect()->intended('/admin/dashboard')
                    ->with('success', 'Selamat datang kembali, ' . $user->username . '!'),
                'kasir' => redirect()->intended('/kasir/dashboard')
                    ->with('success', 'Selamat datang kembali, ' . $user->username . '!'),
                'owner' => redirect()->intended('/owner/dashboard')
                    ->with('success', 'Selamat datang kembali, ' . $user->username . '!'),
                default => redirect()->intended('/dashboard')
                    ->with('success', 'Selamat datang kembali, ' . $user->username . '!')
            };
        }

        // Jika password salah
        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $response = redirect('/login')->with('status', 'Anda telah berhasil logout.');
        $response->withCookie(\Cookie::forget('remember_web_' . hash('sha256', config('app.key'))));

        return $response;
    }
}