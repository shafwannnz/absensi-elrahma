<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Memastikan jam absen masuk benar-benar mengikuti pengaturan admin (WIB). */
class StudentCheckInTest extends TestCase
{
    use RefreshDatabase;

    /** JPEG 1x1 valid (160 byte) untuk lolos validasi selfie. */
    private const JPEG_DATA_URL = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPDc0MzL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AVN//2Q==';

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'Asia/Jakarta']);
        Storage::fake('local');

        // Pengaturan default: masuk 08:00–12:00, tepat waktu s.d. 09:00 WIB.
        CampusSetting::create(config('absensi.defaults'));

        $this->student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'face_descriptor' => array_fill(0, 128, 0),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_check_in_is_rejected_before_configured_open_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 07:59:00', 'Asia/Jakarta'));

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.store'), $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Absen masuk belum dibuka. Dibuka pukul 08:00 WIB.');
    }

    public function test_check_in_succeeds_within_configured_window(): void
    {
        // Regresi: dulu service memakai fallback 01:00–05:00 sehingga 08:15 selalu ditolak.
        Carbon::setTestNow(Carbon::parse('2026-10-24 08:15:00', 'Asia/Jakarta'));

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.store'), $this->validPayload())
            ->assertOk()
            ->assertJsonPath('mode', 'out')
            ->assertJsonPath('label', 'Hadir');

        $attendance = Attendance::where('user_id', $this->student->id)->firstOrFail();

        $this->assertSame(Attendance::STATUS_PRESENT, $attendance->status);
        $this->assertSame('08:15', $attendance->checked_in_at->format('H:i'));
        $this->assertNotNull($attendance->photo_path);
        Storage::disk('local')->assertExists($attendance->photo_path);
    }

    public function test_check_in_after_on_time_limit_is_late(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 10:00:00', 'Asia/Jakarta'));

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.store'), $this->validPayload())
            ->assertOk()
            ->assertJsonPath('label', 'Terlambat');

        $this->assertSame(
            Attendance::STATUS_LATE,
            Attendance::where('user_id', $this->student->id)->value('status')
        );
    }

    public function test_check_in_is_rejected_after_configured_close_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 12:01:00', 'Asia/Jakarta'));

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.store'), $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Absen masuk sudah ditutup pukul 12:00 WIB.');
    }

    private function validPayload(): array
    {
        return [
            'latitude' => config('absensi.defaults.latitude'),
            'longitude' => config('absensi.defaults.longitude'),
            'accuracy' => 10,
            'descriptor' => array_fill(0, 128, 0),
            'photo' => self::JPEG_DATA_URL,
        ];
    }
}
