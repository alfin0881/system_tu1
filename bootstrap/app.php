<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tamu yang mengakses halaman butuh-login akan diarahkan ke /login
        $middleware->redirectGuestsTo(fn () => route('login'));

        // User yang sudah login tapi membuka halaman guest (mis. /login) diarahkan ke dashboard
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        // Alias middleware kustom, dipakai untuk membatasi akses per-role
        // (mis. Kepala Sekolah = view only) pada rute-rute fase berikutnya.
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
