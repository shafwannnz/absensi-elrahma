<?php

namespace App\Services;

/**
 * Pencocokan wajah di sisi server.
 * Browser (face-api) mengubah wajah menjadi vektor 128 angka (descriptor);
 * server membandingkan vektor tersebut dengan yang tersimpan.
 */
class FaceService
{
    /** Jarak Euclidean antar dua descriptor. Makin kecil makin mirip. */
    public function distance(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return INF;
        }

        $sum = 0.0;
        foreach ($a as $i => $value) {
            $sum += ((float) $value - (float) $b[$i]) ** 2;
        }

        return sqrt($sum);
    }

    public function isMatch(float $distance): bool
    {
        return $distance <= (float) config('absensi.face.threshold');
    }
}
