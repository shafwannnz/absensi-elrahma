<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Login terpisah: mahasiswa memakai NIM (/login), admin memakai email (/admin/login).
 * Tidak ada registrasi mandiri: akun mahasiswa dibuat oleh admin.
 */
class AuthController extends Controller
{
    public function showStudentLogin(): View
    {
        return view('auth.login', ['role' => User::ROLE_STUDENT]);
    }

    public function showAdminLogin(): View
    {
        return view('auth.login', ['role' => User::ROLE_ADMIN]);
    }

    public function studentLogin(Request $request): RedirectResponse
    {
        return $this->attempt($request, User::ROLE_STUDENT, 'nim', route('student.attendance'));
    }

    public function adminLogin(Request $request): RedirectResponse
    {
        return $this->attempt($request, User::ROLE_ADMIN, 'email', route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $isAdmin = $request->user()?->isAdmin();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isAdmin ? 'admin.login' : 'login');
    }

    private function attempt(Request $request, string $role, string $field, string $home): RedirectResponse
    {
        $input = $request->validate([
            $field     => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Kondisi tambahan 'role' & 'is_active' ikut menjadi WHERE pada pencarian user.
        $ok = Auth::attempt([
            $field      => $input[$field],
            'password'  => $input['password'],
            'role'      => $role,
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $ok) {
            throw ValidationException::withMessages([
                $field => 'Data login salah atau akun dinonaktifkan.',
            ]);
        }

        $request->session()->regenerate(); // cegah session fixation

        return redirect()->intended($home);
    }
}
