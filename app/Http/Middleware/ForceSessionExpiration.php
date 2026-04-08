<?php
// app/Http/Middleware/ForceSessionExpiration.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceSessionExpiration
{
    public function handle(Request $request, Closure $next)
    {
        // Jika ada session yang menyatakan tidak remember, set expire_on_close true
        if ($request->session()->get('no_remember', false)) {
            config(['session.expire_on_close' => true]);
        }
        
        return $next($request);
    }
}