<?php

namespace App\Http\Requests\Concerns;

/**
 * Descriptor wajah dikirim form HTML sebagai string JSON (input hidden).
 * Trait ini mengubahnya menjadi array agar bisa divalidasi (size:128, numeric).
 */
trait DecodesFaceDescriptor
{
    protected function decodeFaceDescriptor(string $key = 'face_descriptor'): void
    {
        $value = $this->input($key);

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->merge([$key => is_array($decoded) ? $decoded : null]);
        }
    }

    /** Aturan validasi descriptor 128 angka. */
    protected function faceDescriptorRules(bool $required = false): array
    {
        return [
            'face_descriptor'   => [$required ? 'required' : 'nullable', 'array', 'size:128'],
            'face_descriptor.*' => ['numeric'],
        ];
    }
}
