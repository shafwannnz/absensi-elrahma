<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CampusSetting;
use App\Models\User;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Seluruh aturan bisnis absensi (masuk & pulang) ada di sini, sehingga controller tetap tipis.
 * Semua validasi diulang di server: data dari browser tidak boleh dipercaya begitu saja.
 */
class AttendanceService
{
    public function __construct(
        private readonly GeoService $geo,
        private readonly FaceService $face,
    ) {
    }

    /**
     * ABSEN MASUK.
     *
     * @param  array<int,float>  $descriptor  descriptor wajah (128 angka) dari browser
     * @throws DomainException  pesan sudah ramah untuk ditampilkan ke mahasiswa
     */
    public function checkIn(User $user, float $lat, float $lng, ?float $accuracy, array $descriptor, string $photoData): Attendance
    {
        $setting = CampusSetting::current();
        $now = now(); // timezone mengikuti APP_TIMEZONE (Asia/Jakarta)

        $this->assertFaceEnrolled($user);

        // Sudah absen masuk hari ini?
        if (Attendance::where('user_id', $user->id)->whereDate('attended_on', $now->toDateString())->exists()) {
            throw new DomainException('Anda sudah absen masuk hari ini.');
        }

        // Dalam rentang jam absen masuk?
        $this->assertWithinWindow($now, $setting->start_time, $setting->end_time, 'Absen masuk');

        // Lokasi & wajah.
        [$distance, $faceDistance] = $this->verifyPresence($user, $setting, $lat, $lng, $accuracy, $descriptor);

        // Simpan foto (setelah semua validasi lolos, agar tidak ada file sampah).
        $photoPath = $this->storePhoto($user, $now, $photoData);

        // Tentukan status: sampai batas tepat waktu = hadir, sesudahnya = terlambat.
        $onTimeTo = $this->timeOn($now, $setting->on_time_until)->endOfMinute();
        $status = $now->lte($onTimeTo) ? Attendance::STATUS_PRESENT : Attendance::STATUS_LATE;

        try {
            return Attendance::create([
                'user_id'       => $user->id,
                'attended_on'   => $now->toDateString(),
                'checked_in_at' => $now,
                'status'        => $status,
                'latitude'      => $lat,
                'longitude'     => $lng,
                'accuracy_m'    => $accuracy !== null ? (int) round($accuracy) : null,
                'distance_m'    => (int) round($distance),
                'face_distance' => round($faceDistance, 4),
                'photo_path'    => $photoPath,
            ]);
        } catch (UniqueConstraintViolationException) {
            Storage::disk('local')->delete($photoPath); // batalkan file yang sudah terlanjur disimpan
            // Double-submit / dua perangkat bersamaan: unique index menjadi pengaman terakhir.
            throw new DomainException('Anda sudah absen masuk hari ini.');
        }
    }

    /**
     * ABSEN PULANG. Syarat: sudah absen masuk hari ini, belum pulang, dan berada di jam pulang.
     *
     * @throws DomainException
     */
    public function checkOut(User $user, float $lat, float $lng, ?float $accuracy, array $descriptor, string $photoData): Attendance
    {
        $setting = CampusSetting::current();
        $now = now();

        $this->assertFaceEnrolled($user);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attended_on', $now->toDateString())
            ->first();

        if (! $attendance) {
            throw new DomainException('Anda belum absen masuk hari ini, jadi absen pulang belum bisa dilakukan.');
        }
        if ($attendance->checked_out_at) {
            throw new DomainException('Anda sudah absen pulang hari ini.');
        }

        $this->assertWithinWindow($now, $setting->checkout_start, $setting->checkout_end, 'Absen pulang');

        [$distance, $faceDistance] = $this->verifyPresence($user, $setting, $lat, $lng, $accuracy, $descriptor);

        $photoPath = $this->storePhoto($user, $now, $photoData, 'out');

        // Update atomik: hanya berhasil jika kolom checked_out_at masih kosong.
        // Jika dua permintaan masuk bersamaan, hanya satu yang menang.
        $updated = Attendance::whereKey($attendance->id)
            ->whereNull('checked_out_at')
            ->update([
                'checked_out_at'    => $now,
                'out_latitude'      => $lat,
                'out_longitude'     => $lng,
                'out_accuracy_m'    => $accuracy !== null ? (int) round($accuracy) : null,
                'out_distance_m'    => (int) round($distance),
                'out_face_distance' => round($faceDistance, 4),
                'out_photo_path'    => $photoPath,
            ]);

        if ($updated === 0) {
            Storage::disk('local')->delete($photoPath);
            throw new DomainException('Anda sudah absen pulang hari ini.');
        }

        return $attendance->fresh();
    }

    // ------------------------------------------------------------------
    // Pemeriksaan bersama (dipakai absen masuk & pulang)
    // ------------------------------------------------------------------

    private function assertFaceEnrolled(User $user): void
    {
        if (! $user->hasFace()) {
            throw new DomainException('Wajah Anda belum didaftarkan. Daftarkan wajah di menu "Daftar Wajah".');
        }
    }

    /** Pastikan waktu sekarang berada di antara jam buka dan jam tutup (format "H:i"). */
    private function assertWithinWindow(Carbon $now, string $from, string $to, string $label): void
    {
        $opensAt  = $this->timeOn($now, $from)->startOfMinute();
        $closesAt = $this->timeOn($now, $to)->endOfMinute();

        if ($now->lt($opensAt)) {
            throw new DomainException("{$label} belum dibuka. Dibuka pukul {$from} WIB.");
        }
        if ($now->gt($closesAt)) {
            throw new DomainException("{$label} sudah ditutup pukul {$to} WIB.");
        }
    }

    /** Tanggal hari ini dengan jam tertentu ("H:i"). */
    private function timeOn(Carbon $now, string $time): Carbon
    {
        return $now->copy()->setTimeFromTimeString($time);
    }

    /**
     * Cek akurasi GPS, radius kampus, dan kecocokan wajah.
     *
     * @return array{0: float, 1: float}  [jarak ke kampus (m), jarak wajah]
     */
    private function verifyPresence(User $user, CampusSetting $setting, float $lat, float $lng, ?float $accuracy, array $descriptor): array
    {
        // Akurasi GPS cukup baik?
        $maxAccuracy = config('absensi.max_gps_accuracy_m');
        if ($accuracy !== null && $accuracy > $maxAccuracy) {
            throw new DomainException(
                'Sinyal GPS kurang akurat (±'.round($accuracy).' m). Aktifkan lokasi presisi tinggi dan coba lagi.'
            );
        }

        // Dalam radius kampus?
        $distance = $this->geo->distanceInMeters($lat, $lng, $setting->latitude, $setting->longitude);
        if ($distance > $setting->radius_m) {
            throw new DomainException(sprintf(
                'Anda berada %d m dari kampus. Batas maksimal %d m.', round($distance), $setting->radius_m
            ));
        }

        // Wajah cocok?
        $faceDistance = $this->face->distance($user->face_descriptor, $descriptor);
        if (! $this->face->isMatch($faceDistance)) {
            throw new DomainException('Wajah tidak cocok dengan data yang terdaftar. Coba lagi dengan pencahayaan lebih baik.');
        }

        return [$distance, $faceDistance];
    }

    /** Simpan selfie (data URL JPEG) ke disk privat dan kembalikan path-nya. */
    private function storePhoto(User $user, Carbon $now, string $dataUrl, string $suffix = ''): string
    {
        $comma  = strpos($dataUrl, ',');
        $binary = $comma === false ? false : base64_decode(substr($dataUrl, $comma + 1), true);

        // Jangan percaya awalan dari browser: pastikan isinya benar-benar JPEG & ukurannya wajar.
        $info = $binary === false ? false : @getimagesizefromstring($binary);
        if ($info === false || $info['mime'] !== 'image/jpeg' || strlen($binary) > 500 * 1024) {
            throw new DomainException('Foto selfie tidak valid. Muat ulang halaman dan coba lagi.');
        }

        // Nama file dibuat server (bukan dari input pengguna) -> aman dari path traversal.
        $path = sprintf(
            'attendance-photos/%s/%d-%s%s.jpg',
            $now->format('Y-m'), $user->id, $now->format('Ymd-His'), $suffix !== '' ? "-{$suffix}" : ''
        );
        Storage::disk('local')->put($path, $binary);

        return $path;
    }
}
