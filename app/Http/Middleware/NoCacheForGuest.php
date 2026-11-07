<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tambahkan header no-cache ke halaman guest (login).
 * Ini mencegah browser menampilkan halaman login dari cache
 * saat user yang sudah login menekan tombol Back.
 */
class NoCacheForGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma',        'no-cache');
        $response->headers->set('Expires',       '0');

        return $response;
    }
}

