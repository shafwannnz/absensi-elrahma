<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckInRequest;
use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Services\AttendanceService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /** Halaman absen: kamera + lokasi. Mode menyesuaikan kondisi hari ini. */
    public function create(Request $request): View
    {
        $user  = $request->user();
        $today = Attendance::where('user_id', $user->id)->today()->first();

        return view('student.attendance', [
            'setting' => CampusSetting::current(),
            'today'   => $today,
            'hasFace' => $user->hasFace(),
            // in = belum absen masuk | out = sudah masuk, belum pulang | done = keduanya selesai
            'mode'    => ! $today ? 'in' : ($today->checked_out_at ? 'done' : 'out'),
        ]);
    }

    /** Absen MASUK (dipanggil attendance.js). */
    public function store(CheckInRequest $request, AttendanceService $service): JsonResponse
    {
        try {
            $attendance = $service->checkIn(...$this->payload($request));
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message'  => 'Absen masuk berhasil dicatat.',
            'status'   => $attendance->status,
            'label'    => $attendance->status_label,
            'time'     => $attendance->checked_in_at->format('H:i'),
            'distance' => $attendance->distance_m,
        ]);
    }

    /** Absen PULANG (dipanggil attendance.js). Payload sama dengan absen masuk. */
    public function checkOut(CheckInRequest $request, AttendanceService $service): JsonResponse
    {
        try {
            $attendance = $service->checkOut(...$this->payload($request));
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message'  => 'Absen pulang berhasil dicatat.',
            'time'     => $attendance->checked_out_at->format('H:i'),
            'distance' => $attendance->out_distance_m,
        ]);
    }

    /** Riwayat absensi milik mahasiswa yang sedang login. */
    public function history(Request $request): View
    {
        $query = Attendance::where('user_id', $request->user()->id);

        return view('student.history', [
            'attendances' => (clone $query)->latest('attended_on')->paginate(10),
            'summary'     => [
                'total'   => (clone $query)->count(),
                'present' => (clone $query)->where('status', Attendance::STATUS_PRESENT)->count(),
                'late'    => (clone $query)->where('status', Attendance::STATUS_LATE)->count(),
            ],
        ]);
    }

    /** Susun argumen service dari request (urutan sesuai parameter checkIn/checkOut). */
    private function payload(CheckInRequest $request): array
    {
        return [
            $request->user(),
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
            $request->filled('accuracy') ? (float) $request->input('accuracy') : null,
            $request->input('descriptor'),
            $request->input('photo'),
        ];
    }
}
