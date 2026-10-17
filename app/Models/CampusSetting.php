<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusSetting extends Model
{
    protected $fillable = [
        'name', 'latitude', 'longitude', 'radius_m',
        'start_time', 'on_time_until', 'end_time', 'checkout_start', 'checkout_end',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'float',
            'longitude' => 'float',
            'radius_m'  => 'integer',
        ];
    }

    /** Ambil pengaturan aktif; jika belum ada, buat dari nilai default config. */
    public static function current(): self
    {
        return static::query()->first() ?? static::create(config('absensi.defaults'));
    }
}
