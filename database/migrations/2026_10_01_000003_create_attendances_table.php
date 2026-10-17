<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attended_on');                 // tanggal absen (WIB)
            $table->dateTime('checked_in_at');           // waktu absen
            $table->string('status', 20);                // hadir | terlambat
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedSmallInteger('accuracy_m')->nullable(); // akurasi GPS perangkat
            $table->unsignedInteger('distance_m');       // jarak ke kampus saat absen
            $table->decimal('face_distance', 5, 4);      // makin kecil = makin mirip
            $table->timestamps();

            // Satu mahasiswa hanya boleh absen sekali per hari (dijaga di level database).
            $table->unique(['user_id', 'attended_on']);
            $table->index(['attended_on', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
