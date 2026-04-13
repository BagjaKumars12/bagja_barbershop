<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // ... jika ada routing lain
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias untuk middleware yang akan digunakan di route
        $middleware->alias([
            'role' => CheckRole::class,
            'log.activity' => LogUserActivity::class,
        ]);

        // **Tambahkan middleware LogUserActivity ke global stack**
        $middleware->append(LogUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();