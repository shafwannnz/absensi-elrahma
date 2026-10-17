<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalStudents = User::students()->count();
        $presentToday  = Attendance::today()->where('status', Attendance::STATUS_PRESENT)->count();
        $lateToday     = Attendance::today()->where('status', Attendance::STATUS_LATE)->count();

        $stats = [
            'total_attendance' => Attendance::count(),
            'present_today'    => $presentToday,
            'late_today'       => $lateToday,
            'total_students'   => $totalStudents,
            'absent_today'     => max($totalStudents - $presentToday - $lateToday, 0),
        ];

        return view('admin.dashboard', [
            'stats'   => $stats,
            'chart'   => $this->lastSevenDays(),
            'recent'  => Attendance::with('user')->latest('checked_in_at')->limit(8)->get(),
            'setting' => CampusSetting::current(),
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
            ->groupBy(fn ($row) => $row->attended_on->toDateString());

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
