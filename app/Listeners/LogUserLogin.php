<?php

namespace App\Listeners;

use App\Helpers\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Cache;

class LogUserLogin
{
    public function handle(Login $event)
    {
        $user = $event->user;
        $key = 'login_log_' . $user->id . '_' . request()->ip();
        
        // Cek apakah sudah login-log dalam 2 detik terakhir
        if (Cache::has($key)) {
            return;
        }
        
        Cache::put($key, true, now()->addSeconds(2));
        
        ActivityLogger::log('LOGIN', 'Auth', "User {$user->username} login dari IP " . request()->ip());
    }
}