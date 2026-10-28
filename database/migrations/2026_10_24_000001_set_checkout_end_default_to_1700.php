<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('campus_settings')
            ->where('checkout_end', '16:00')
            ->update(['checkout_end' => '17:00']);
    }

    public function down(): void
    {
        DB::table('campus_settings')
            ->where('checkout_end', '17:00')
            ->update(['checkout_end' => '16:00']);
    }
};
