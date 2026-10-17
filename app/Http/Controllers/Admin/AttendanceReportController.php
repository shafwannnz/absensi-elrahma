<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Laporan absensi: filter, daftar, dan ekspor CSV. */
class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $attendances = $this->query($request)->paginate(15)->withQueryString();

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'filters'     => $request->only(['from', 'to', 'status', 'q']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'absensi-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
            fputcsv($out, ['Tanggal', 'Jam Masuk (WIB)', 'Jam Pulang (WIB)', 'NIM', 'Nama', 'Program Studi', 'Status', 'Jarak (m)', 'Akurasi GPS (m)', 'Jarak Wajah']);

            // cursor() agar hemat memori pada data besar
            foreach ($this->query($request)->cursor() as $a) {
                fputcsv($out, [
                    $a->attended_on->format('Y-m-d'),
                    $a->checked_in_at->format('H:i:s'),
                    $a->checked_out_at?->format('H:i:s'),
                    $a->user->nim,
                    $a->user->name,
                    $a->user->study_program,
                    $a->status_label,
                    $a->distance_m,
                    $a->accuracy_m,
                    $a->face_distance,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Tampilkan foto selfie. Hanya admin (route dilindungi middleware role:admin). */
    public function photo(Attendance $attendance, string $type = 'in'): StreamedResponse
    {
        // type 'in' = foto absen masuk, 'out' = foto absen pulang
        $path = $type === 'out' ? $attendance->out_photo_path : $attendance->photo_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control'       => 'private, max-age=86400',
            'Content-Disposition' => 'inline',
        ]);
    }

    private function query(Request $request): Builder
    {
        $q = trim((string) $request->query('q'));

        return Attendance::query()
            ->with('user')
            ->when($request->filled('from'), fn ($qb) => $qb->whereDate('attended_on', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($qb) => $qb->whereDate('attended_on', '<=', $request->query('to')))
            ->when(in_array($request->query('status'), [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE], true),
                fn ($qb) => $qb->where('status', $request->query('status')))
            ->when($q !== '', fn ($qb) => $qb->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%")))
            ->latest('checked_in_at');
    }
}
