<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\DecodesFaceDescriptor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi tambah & ubah data mahasiswa oleh admin. */
class StudentRequest extends FormRequest
{
    use DecodesFaceDescriptor;

    public function authorize(): bool
    {
        return true; // akses sudah dijaga middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->decodeFaceDescriptor();
    }

    public function rules(): array
    {
        $student = $this->route('student'); // null saat create
        $isUpdate = $student !== null;

        return [
            'name'          => ['required', 'string', 'max:120'],
            'nim'           => ['required', 'string', 'max:20', Rule::unique('users', 'nim')->ignore($student?->id)],
            'email'         => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($student?->id)],
            'phone'         => ['nullable', 'string', 'max:20'],
            'study_program' => ['nullable', 'string', 'max:100'],
            'password'      => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active'     => ['boolean'],
        ] + $this->faceDescriptorRules();
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama', 'nim' => 'NIM', 'email' => 'email', 'phone' => 'no. HP',
            'study_program' => 'program studi', 'password' => 'kata sandi',
            'face_descriptor' => 'data wajah',
        ];
    }
}
