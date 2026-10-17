<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaceEnrollRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaceController extends Controller
{
    public function edit(Request $request): View
    {
        return view('student.face', ['hasFace' => $request->user()->hasFace()]);
    }

    public function store(FaceEnrollRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Setelah terdaftar, hanya admin yang boleh mereset (mencegah penggantian wajah oleh orang lain).
        if ($user->hasFace()) {
            return back()->withErrors(['face_descriptor' => 'Wajah sudah terdaftar. Minta admin untuk mereset jika perlu.']);
        }

        $user->update(['face_descriptor' => $request->validated('face_descriptor')]);

        return redirect()->route('student.attendance')->with('success', 'Wajah berhasil didaftarkan. Anda bisa absen sekarang.');
    }
}
