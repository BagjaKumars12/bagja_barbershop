<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public static function log($activity, $module, $description = null)
    {
        if (!Auth::check()) return;

        $user = Auth::user();

        try {
            ActivityLog::create([
                'user_name'    => $user->username,
                'user_role'    => $user->role,
                'activity'     => $activity,
                'module'       => $module,
                'description'  => $description,
                'ip_address'   => Request::ip(),
                'user_agent'   => Request::userAgent(),
                'created_at'   => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal simpan activity log: ' . $e->getMessage());
        }
    }
}