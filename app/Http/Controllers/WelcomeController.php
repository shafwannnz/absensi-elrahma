<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\View\View;

/**
 * Halaman publik (tanpa login) yang menampilkan ringkasan informasi
 * sistem absensi STIKOM El Rahma.
 *
 * Data yang ditampilkan bersifat umum (jumlah mahasiswa, statistik
 * kehadiran, jam operasional) tanpa mengekspos data sensitif.
 */
class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        $setting       = CampusSetting::current();
        $totalStudents = User::students()->count();
        $presentToday  = Attendance::today()->where('status', Attendance::STATUS_PRESENT)->count();
        $lateToday     = Attendance::today()->where('status', Attendance::STATUS_LATE)->count();
        $absentToday   = max($totalStudents - $presentToday - $lateToday, 0);

        // Persentase kehadiran hari ini (untuk progress ring)
        $attendanceRate = $totalStudents > 0
            ? round(($presentToday + $lateToday) / $totalStudents * 100)
            : 0;

        return view('welcome', [
            'setting'        => $setting,
            'attendanceRate' => $attendanceRate,
            'chart'          => $this->lastSevenDays(),
        ]);
    }

    /** Rekap 7 hari terakhir (hadir & terlambat) untuk grafik batang. */
    private function lastSevenDays(): array
    {
        $from = now()->subDays(6)->startOfDay();

        $rows = Attendance::query()
            ->selectRaw('attended_on, status, COUNT(*) as total')
            ->where('attended_on', '>=', $from->toDateString())
            ->groupBy('attended_on', 'status')
            ->get()
            ->groupBy(fn($row) => $row->attended_on->toDateString());

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date  = $from->copy()->addDays($i);
            $group = $rows->get($date->toDateString(), collect());

            $days[] = [
                'label'   => $date->translatedFormat('D d/m'),
                'present' => (int) $group->firstWhere('status', Attendance::STATUS_PRESENT)?->total,
                'late'    => (int) $group->firstWhere('status', Attendance::STATUS_LATE)?->total,
            ];
        }

        return $days;
    }
}
