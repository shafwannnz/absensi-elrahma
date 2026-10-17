<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DecodesFaceDescriptor;
use Illuminate\Foundation\Http\FormRequest;

/** Mahasiswa mendaftarkan wajahnya sendiri (hanya jika belum pernah). */
class FaceEnrollRequest extends FormRequest
{
    use DecodesFaceDescriptor;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->decodeFaceDescriptor();
    }

    public function rules(): array
    {
        return $this->faceDescriptorRules(required: true);
    }

    public function messages(): array
    {
        return ['face_descriptor.required' => 'Ambil foto wajah terlebih dahulu sebelum menyimpan.'];
    }
}
