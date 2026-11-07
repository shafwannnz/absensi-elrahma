<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menggugurkan session jika user tidak melakukan aktivitas apapun
 * selama SESSION_TIMEOUT_MINUTES menit (default 5).
 *
 * Berbeda dengan SESSION_LIFETIME di config (yang dihitung dari
 * kapan session dibuat), middleware ini menghitung dari aktivitas
 * TERAKHIR user — sehingga selalu 5 menit sejak request terakhir.
 */
class SessionTimeout
{
    /** Batas inaktivitas dalam menit. */
    private const TIMEOUT_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        // Hanya berlaku untuk user yang sudah login.
        if (! Auth::check()) {
            return $next($request);
        }

        $lastActivity = $request->session()->get('_last_activity_at');
        $now          = now()->timestamp;
        $limitSeconds = self::TIMEOUT_MINUTES * 60;

        // Jika sudah lebih dari batas waktu inaktivitas → logout paksa.
        if ($lastActivity !== null && ($now - $lastActivity) > $limitSeconds) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $isAdmin = $request->is('admin', 'admin/*');

            return redirect()
                ->route($isAdmin ? 'admin.login' : 'login')
                ->withErrors([
                    $isAdmin ? 'email' : 'nim' =>
                        'Sesi Anda telah berakhir karena tidak ada aktivitas selama '
                        . self::TIMEOUT_MINUTES . ' menit. Silakan login kembali.',
                ]);
        }

        // Perbarui timestamp aktivitas terakhir setiap request.
        $request->session()->put('_last_activity_at', $now);

        $response = $next($request);

        // Tambahkan header no-cache agar browser tidak menyimpan halaman
        // yang membutuhkan autentikasi — sehingga tombol Back tidak bisa
        // menampilkan halaman lama setelah session berakhir / logout.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma',        'no-cache');
        $response->headers->set('Expires',       '0');

        return $response;
    }
}
