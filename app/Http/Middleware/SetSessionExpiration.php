<?php
// app/Http/Middleware/SetSessionExpiration.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetSessionExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if (Auth::check()) {
            $user = Auth::user();
            $remember = $request->has('remember');
            
            // Set cookie expiration berdasarkan remember me
            if (!$remember) {
                // Jika tidak remember me, session expire saat browser ditutup
                config(['session.expire_on_close' => true]);
            }
        }
        
        return $response;
    }
}