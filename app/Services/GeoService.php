<?php

namespace App\Services;

/** Perhitungan geografis. */
class GeoService
{
    private const EARTH_RADIUS_M = 6371000;

    /** Jarak dua titik koordinat (meter) memakai rumus Haversine. */
    public function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_M * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
