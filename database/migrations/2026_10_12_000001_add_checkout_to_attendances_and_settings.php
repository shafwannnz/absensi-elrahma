<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Menambah data absen pulang pada attendances dan jam pulang pada campus_settings. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('checked_out_at')->nullable()->after('checked_in_at'); // null = belum pulang
            $table->decimal('out_latitude', 10, 7)->nullable();
            $table->decimal('out_longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('out_accuracy_m')->nullable();
            $table->unsignedInteger('out_distance_m')->nullable();
            $table->decimal('out_face_distance', 5, 4)->nullable();
            $table->string('out_photo_path')->nullable();
        });

        // Default 15:00-16:00 juga berlaku untuk baris pengaturan yang sudah ada.
        Schema::table('campus_settings', function (Blueprint $table) {
            $table->string('checkout_start', 5)->default('15:00');
            $table->string('checkout_end', 5)->default('16:00');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'checked_out_at', 'out_latitude', 'out_longitude', 'out_accuracy_m',
                'out_distance_m', 'out_face_distance', 'out_photo_path',
            ]);
        });
        Schema::table('campus_settings', function (Blueprint $table) {
            $table->dropColumn(['checkout_start', 'checkout_end']);
        });
    }
};