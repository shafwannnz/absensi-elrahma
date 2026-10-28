<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StudentCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'Asia/Jakarta']);
        Carbon::setTestNow(Carbon::parse('2026-10-24 14:59:00', 'Asia/Jakarta'));

        CampusSetting::create(config('absensi.defaults'));
        $this->student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'face_descriptor' => array_fill(0, 128, 0),
        ]);

        Attendance::create([
            'user_id' => $this->student->id,
            'attended_on' => Carbon::now('Asia/Jakarta')->toDateString(),
            'checked_in_at' => Carbon::now('Asia/Jakarta')->setTime(8, 0),
            'status' => Attendance::STATUS_PRESENT,
            'latitude' => config('absensi.defaults.latitude'),
            'longitude' => config('absensi.defaults.longitude'),
            'distance_m' => 0,
            'face_distance' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_attendance_page_offers_checkout_after_checkin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 15:30:00', 'Asia/Jakarta'));

        $this->actingAs($this->student)
            ->get(route('student.attendance'))
            ->assertOk()
            ->assertSee('Absen pulang sekarang')
            ->assertSee(route('student.attendance.checkout'));
    }

    public function test_checkout_is_rejected_before_1500_and_after_1700_wib(): void
    {
        $payload = [
            'latitude' => config('absensi.defaults.latitude'),
            'longitude' => config('absensi.defaults.longitude'),
            'descriptor' => array_fill(0, 128, 0),
            'photo' => 'data:image/jpeg;base64,',
        ];

        foreach ([
            ['time' => '2026-10-24 14:59:00', 'message' => 'Absen pulang belum dibuka. Dibuka pukul 15:00 WIB.'],
            ['time' => '2026-10-24 17:01:00', 'message' => 'Absen pulang sudah ditutup pukul 17:00 WIB.'],
        ] as $case) {
            Carbon::setTestNow(Carbon::parse($case['time'], 'Asia/Jakarta'));

            $this->actingAs($this->student)
                ->postJson(route('student.attendance.checkout'), $payload)
                ->assertUnprocessable()
                ->assertJsonPath('message', $case['message']);
        }
    }
}
