<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STUDENT = 'student';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nim', 'phone',
        'study_program', 'face_descriptor', 'is_active',
    ];

    // face_descriptor tidak pernah ikut ter-serialize ke JSON/response.
    protected $hidden = ['password', 'remember_token', 'face_descriptor'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',  // otomatis di-hash saat di-set
            'face_descriptor'   => 'array',
            'is_active'         => 'boolean',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_STUDENT);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function hasFace(): bool
    {
        return is_array($this->face_descriptor) && count($this->face_descriptor) === 128;
    }
}
