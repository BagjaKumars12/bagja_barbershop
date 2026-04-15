<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('user_name', 'like', "%{$request->search}%")
                  ->orWhere('activity', 'like', "%{$request->search}%")
                  ->orWhere('module', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('user_role', $request->role);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->paginate(20)->withQueryString();

        $roles = ActivityLog::distinct()->pluck('user_role');
        $modules = ActivityLog::distinct()->pluck('module');

        return view('admin.log_activity.index', compact('logs', 'roles', 'modules'));
    }

    public function show($id)
    {
        $log = ActivityLog::findOrFail($id);
        return view('admin.log_activity.detail', compact('log'));
    }
}