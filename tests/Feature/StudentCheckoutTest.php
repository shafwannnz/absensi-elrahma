<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentCheckoutTest extends TestCase
{
    use RefreshDatabase;

    /** JPEG 1x1 valid (160 byte) untuk menguji penyimpanan snapshot selfie. */
    private const JPEG_DATA_URL = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPDc0MzL/wAALCAABAAEBAREA/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8AVN//2Q==';

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

    public function test_checkout_success_stores_photo_and_marks_attendance_complete(): void
    {
        Storage::fake('local');
        Carbon::setTestNow(Carbon::parse('2026-10-24 15:30:00', 'Asia/Jakarta'));

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.checkout'), $this->validPayload())
            ->assertOk()
            ->assertJsonPath('message', 'Absen pulang berhasil dicatat.')
            ->assertJsonPath('mode', 'done');

        $attendance = Attendance::where('user_id', $this->student->id)->firstOrFail();

        $this->assertNotNull($attendance->checked_out_at);
        $this->assertSame(0, $attendance->out_distance_m);
        $this->assertNotNull($attendance->out_photo_path);
        Storage::disk('local')->assertExists($attendance->out_photo_path);
    }

    public function test_checkout_is_rejected_outside_campus_radius(): void
    {
        Storage::fake('local');
        Carbon::setTestNow(Carbon::parse('2026-10-24 15:30:00', 'Asia/Jakarta'));

        $payload = $this->validPayload();
        $payload['latitude'] = config('absensi.defaults.latitude') + 0.05; // ±5,5 km dari kampus

        $response = $this->actingAs($this->student)
            ->postJson(route('student.attendance.checkout'), $payload)
            ->assertUnprocessable();

        $this->assertStringContainsString('Batas maksimal 500 m', $response->json('message'));
    }

    public function test_checkout_is_rejected_when_already_checked_out(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 15:30:00', 'Asia/Jakarta'));

        Attendance::where('user_id', $this->student->id)->update([
            'checked_out_at' => Carbon::now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.checkout'), $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Anda sudah melakukan absen pulang hari ini.');
    }

    public function test_checkout_is_rejected_without_check_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 15:30:00', 'Asia/Jakarta'));

        Attendance::where('user_id', $this->student->id)->delete();

        $this->actingAs($this->student)
            ->postJson(route('student.attendance.checkout'), $this->validPayload())
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Anda belum melakukan absen masuk hari ini.');
    }

    public function test_completed_attendance_page_hides_camera_form_and_shows_recap(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-10-24 16:00:00', 'Asia/Jakarta'));

        Attendance::where('user_id', $this->student->id)->update([
            'checked_out_at' => Carbon::now('Asia/Jakarta'),
            'out_distance_m' => 12,
        ]);

        $this->actingAs($this->student)
            ->get(route('student.attendance'))
            ->assertOk()
            ->assertSee('Absensi hari ini selesai')
            ->assertDontSee('id="attendance-app"', false);
    }

    /** Payload valid: tepat di titik kampus, wajah cocok, foto JPEG valid. */
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
