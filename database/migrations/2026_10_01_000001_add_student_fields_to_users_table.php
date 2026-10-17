<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Menambah kolom peran & data mahasiswa pada tabel users bawaan Laravel. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('student')->index()->after('password'); // admin | student
            $table->string('nim', 20)->nullable()->unique()->after('role');
            $table->string('phone', 20)->nullable()->after('nim');
            $table->string('study_program', 100)->nullable()->after('phone');
            $table->json('face_descriptor')->nullable()->after('study_program'); // 128 angka hasil face-api
            $table->boolean('is_active')->default(true)->after('face_descriptor');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nim']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'nim', 'phone', 'study_program', 'face_descriptor', 'is_active']);
        });
    }
};
