<?php

use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);

        // Mode SPA stateful: request /api/v1/* dari origin aplikasi
        // (daftarnya di config/sanctum.php, key 'stateful', mis. localhost:8000)
        // memakai session login web yang sama, sehingga dashboard Blade
        // yang memanggil API memakai cookie berhasil terautentikasi
        // tanpa perlu token Bearer.
        $middleware->statefulApi();

        // Web routes menggunakan pure Laravel session authentication
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
