<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Membatasi route berdasarkan peran: role:admin atau role:student. */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Akun dinonaktifkan admin -> paksa keluar.
        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['nim' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        }

        // Peran tidak sesuai -> arahkan ke beranda miliknya sendiri.
        if (! $user || $user->role !== $role) {
            if ($request->expectsJson()) {
                abort(403, 'Anda tidak memiliki akses.');
            }

            return redirect($user?->isAdmin() ? route('admin.dashboard') : route('student.attendance'));
        }

        return $next($request);
    }
}
