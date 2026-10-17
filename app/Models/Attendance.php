<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    public const STATUS_PRESENT = 'hadir';
    public const STATUS_LATE = 'terlambat';

    protected $fillable = [
        'user_id', 'attended_on', 'checked_in_at', 'status', 'latitude',
        'longitude', 'accuracy_m', 'distance_m', 'face_distance', 'photo_path',
        'checked_out_at', 'out_latitude', 'out_longitude', 'out_accuracy_m',
        'out_distance_m', 'out_face_distance', 'out_photo_path',
    ];

    protected function casts(): array
    {
        return [
            'attended_on'   => 'date',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'latitude'      => 'float',
            'longitude'     => 'float',
            'face_distance' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Absensi pada hari ini (zona waktu aplikasi = WIB). */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('attended_on', now()->toDateString());
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === self::STATUS_LATE ? 'Terlambat' : 'Hadir';
    }
}
