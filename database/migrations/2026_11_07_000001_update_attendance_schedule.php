<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Update jadwal absensi:
 *   - Masuk  : buka 06:00, batas tepat waktu 07:00, tutup 08:00
 *   - Pulang : buka 15:00, tutup 17:00
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('campus_settings')->update([
            'start_time'     => '06:00',
            'on_time_until'  => '07:00',
            'end_time'       => '08:00',
            'checkout_start' => '15:00',
            'checkout_end'   => '17:00',
        ]);
    }

    public function down(): void
    {
        // Kembalikan ke jadwal sebelumnya jika rollback
        DB::table('campus_settings')->update([
            'start_time'     => '08:00',
            'on_time_until'  => '09:00',
            'end_time'       => '12:00',
            'checkout_start' => '15:00',
            'checkout_end'   => '17:00',
        ]);
    }
};

