<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya satu baris: pengaturan lokasi & jam absensi kampus.
        Schema::create('campus_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_m')->default(500);
            $table->string('start_time', 5)->default('08:00');
            $table->string('on_time_until', 5)->default('09:00');
            $table->string('end_time', 5)->default('12:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_settings');
    }
};
