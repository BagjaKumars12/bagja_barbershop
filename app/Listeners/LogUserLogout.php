<?php

namespace App\Listeners;

use App\Helpers\ActivityLogger;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;

class LogUserLogout
{
    public function handle(Logout $event)
    {
        $user = $event->user;
        if (!$user) return;
        
        $key = 'logout_log_' . $user->id;
        if (Cache::has($key)) {
            return;
        }
        
        Cache::put($key, true, now()->addSeconds(2));
        
        ActivityLogger::log('LOGOUT', 'Auth', "User {$user->username} logout");
    }
}