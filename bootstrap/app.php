<?php

use App\Http\Middleware\EnsureRole;
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
        // Wajib agar HTTPS terdeteksi benar saat diakses lewat tunnel (ngrok / cloudflared).
        // Di produksi, ganti '*' dengan IP/CIDR reverse-proxy Anda.
        $middleware->trustProxies(at: '*');

        // Alias middleware: ->middleware('role:admin') / ->middleware('role:student')
        $middleware->alias(['role' => EnsureRole::class]);

        // Tamu yang membuka halaman terlindungi diarahkan ke login yang sesuai.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));

        // Pengguna yang sudah login diarahkan ke beranda sesuai perannya.
        $middleware->redirectUsersTo(fn () => auth()->user()?->isAdmin()
            ? route('admin.dashboard')
            : route('student.attendance'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
