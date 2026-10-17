<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** CRUD mahasiswa. Registrasi mahasiswa hanya dilakukan oleh admin di sini. */
class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $students = User::students()
            ->withCount('attendances')
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('study_program', 'like', "%{$search}%");
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create(): View
    {
        return view('admin.students.create');
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['role'] = User::ROLE_STUDENT;
        $data['is_active'] = $request->boolean('is_active', true);

        User::create($data);

        return redirect()->route('admin.students.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function edit(User $student): View
    {
        $this->ensureStudent($student);

        return view('admin.students.edit', compact('student'));
    }

    public function update(StudentRequest $request, User $student): RedirectResponse
    {
        $this->ensureStudent($student);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        // Password / wajah kosong = tidak diubah.
        foreach (['password', 'face_descriptor'] as $key) {
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }

        $student->update($data);

        return redirect()->route('admin.students.index')->with('success', 'Data mahasiswa diperbarui.');
    }

    public function destroy(User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        // Hapus file foto dulu; baris absensi ikut terhapus oleh cascade database.
        $files = $student->attendances()->get(['photo_path', 'out_photo_path'])
            ->flatMap(fn ($a) => [$a->photo_path, $a->out_photo_path])
            ->filter()->all();
        Storage::disk('local')->delete($files);
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Mahasiswa dihapus.');
    }

    /** Hapus data wajah agar mahasiswa bisa mendaftar ulang. */
    public function resetFace(User $student): RedirectResponse
    {
        $this->ensureStudent($student);
        $student->update(['face_descriptor' => null]);

        return back()->with('success', "Data wajah {$student->name} direset.");
    }

    // Route binding memakai model User; pastikan yang diakses bukan akun admin.
    private function ensureStudent(User $user): void
    {
        abort_unless($user->isStudent(), 404);
    }
}
