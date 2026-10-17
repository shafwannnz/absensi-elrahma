<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi pengaturan lokasi & jam absensi. */
class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:100'],
            'latitude'      => ['required', 'numeric', 'between:-90,90'],
            'longitude'     => ['required', 'numeric', 'between:-180,180'],
            'radius_m'      => ['required', 'integer', 'min:50', 'max:5000'],
            'start_time'    => ['required', 'date_format:H:i'],
            'on_time_until' => ['required', 'date_format:H:i', 'after:start_time'],
            'end_time'      => ['required', 'date_format:H:i', 'after_or_equal:on_time_until'],
            'checkout_start' => ['required', 'date_format:H:i', 'after_or_equal:end_time'],
            'checkout_end'   => ['required', 'date_format:H:i', 'after:checkout_start'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lokasi', 'radius_m' => 'radius', 'start_time' => 'jam buka',
            'on_time_until' => 'batas tepat waktu', 'end_time' => 'jam tutup',
            'checkout_start' => 'jam buka absen pulang', 'checkout_end' => 'jam tutup absen pulang',
        ];
    }
}
