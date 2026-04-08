<?php
// app/Http/Middleware/CheckRememberMe.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRememberMe
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cek apakah user login via remember me
            if ($user->remember_token && !session()->has('via_remember')) {
                session()->put('via_remember', true);
            }
        }

        return $next($request);
    }
}