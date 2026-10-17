<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Payload absensi dari browser (JSON). */
class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude'     => ['required', 'numeric', 'between:-90,90'],
            'longitude'    => ['required', 'numeric', 'between:-180,180'],
            'accuracy'     => ['nullable', 'numeric', 'min:0'],
            'descriptor'   => ['required', 'array', 'size:128'],
            'descriptor.*' => ['numeric'],
            'photo' => ['required', 'string', 'starts_with:data:image/jpeg;base64,', 'max:700000'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required'   => 'Lokasi belum terdeteksi. Aktifkan GPS dan izinkan akses lokasi.',
            'longitude.required'  => 'Lokasi belum terdeteksi. Aktifkan GPS dan izinkan akses lokasi.',
            'descriptor.required' => 'Wajah belum terdeteksi. Hadapkan wajah ke kamera.',
            'descriptor.size'     => 'Data wajah tidak valid. Muat ulang halaman dan coba lagi.',
            'photo.required' => 'Foto selfie gagal diambil. Muat ulang halaman dan coba lagi.',
        ];
    }
}
