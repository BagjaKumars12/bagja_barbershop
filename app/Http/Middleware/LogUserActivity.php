<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class LogUserActivity
{
    /**
     * Mapping HTTP method ke action name
     */
    protected $actionMap = [
        'GET'    => 'READ',
        'POST'   => 'CREATE',
        'PUT'    => 'UPDATE',
        'PATCH'  => 'UPDATE',
        'DELETE' => 'DELETE',
    ];

    /**
     * Method HTTP yang akan dicatat (selain GET)
     */
    protected $loggableMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Route GET yang tetap dicatat (READ)
     */
    protected $readableRoutes = [
        '*/customers*',
        '*/barbers*',
        '*/services*',
        '*/users*',
        '*/transactions*',
        '*/bookings*',
        '*/reports*',
        '*/activity-logs*',
        '*/dashboard',
        '*/profile',
    ];

    /**
     * Route yang tidak perlu dicatat
     */
    protected $excludeRoutes = [
        'login',
        'logout',
        'livewire/*',
        'debugbar/*',
        'telescope/*',
        '_ignition/*',
        'api/*',
        'owner/activity-logs*', // hindari infinite loop
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldLog($request)) {
            $user = $request->user();
            if ($user) {
                $method = $request->method();
                $action = $this->getActionName($method);
                $module = $this->getModuleFromUrl($request->path());
                $description = $this->buildDescription($request, $method, $module);

                ActivityLogger::log($action, $module, $description);
            }
        }

        return $response;
    }

    /**
     * Konversi method HTTP ke nama aksi
     */
    protected function getActionName(string $method): string
    {
        return $this->actionMap[$method] ?? $method;
    }

    /**
     * Cek apakah request perlu dicatat
     */
    protected function shouldLog(Request $request): bool
    {
        $method = $request->method();

        // Exclude route tertentu
        foreach ($this->excludeRoutes as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        // Log untuk method POST, PUT, PATCH, DELETE (CREATE, UPDATE, DELETE)
        if (in_array($method, $this->loggableMethods)) {
            return true;
        }

        // Log untuk GET hanya jika route termasuk readableRoutes (READ)
        if ($method === 'GET') {
            foreach ($this->readableRoutes as $pattern) {
                if ($request->is($pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Dapatkan nama modul dari URL
     */
    protected function getModuleFromUrl(string $path): string
    {
        $mapping = [
            'transactions'  => 'Transaction',
            'customers'     => 'Customer',
            'barbers'       => 'Barber',
            'services'      => 'Service',
            'users'         => 'User',
            'bookings'      => 'Booking',
            'reports'       => 'Report',
            'profile'       => 'Profile',
            'dashboard'     => 'Dashboard',
            'activity-logs' => 'ActivityLog',
        ];

        foreach ($mapping as $segment => $module) {
            if (str_contains($path, $segment)) {
                return $module;
            }
        }

        return 'General';
    }

    /**
     * Buat deskripsi log yang informatif
     */
    protected function buildDescription(Request $request, string $method, string $module): string
    {
        $url = $request->fullUrl();
        $action = $this->getActionName($method);
        
        // Ambil ID dari route jika ada
        $id = null;
        $route = $request->route();
        if ($route) {
            foreach ($route->parameters() as $key => $value) {
                if (in_array($key, ['id', 'customer', 'barber', 'service', 'user', 'booking', 'transaction'])) {
                    $id = $value;
                    break;
                }
            }
        }

        $summary = "$action $module: $url";
        if ($id) {
            $summary .= " (ID: $id)";
        }

        // Untuk CREATE/UPDATE, sertakan data input (kecuali field sensitif)
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $input = $request->except(['_token', '_method', 'password', 'password_confirmation', 'card_number', 'cvv']);
            if (!empty($input)) {
                $inputSummary = json_encode($input, JSON_UNESCAPED_SLASHES);
                if (strlen($inputSummary) > 200) {
                    $inputSummary = substr($inputSummary, 0, 200) . '...';
                }
                $summary .= " | Data: " . $inputSummary;
            }
        }

        return $summary;
    }
}