<?php

namespace Tests\Unit;

use App\Services\GeoService;
use PHPUnit\Framework\TestCase;

class GeoServiceTest extends TestCase
{
    private const CAMPUS_LAT = -6.590043695245298;
    private const CAMPUS_LNG = 106.78561680228029;

    public function test_distance_to_same_point_is_zero(): void
    {
        $d = (new GeoService())->distanceInMeters(self::CAMPUS_LAT, self::CAMPUS_LNG, self::CAMPUS_LAT, self::CAMPUS_LNG);

        $this->assertEqualsWithDelta(0, $d, 0.001);
    }

    public function test_point_450m_north_is_inside_500m_radius(): void
    {
        // 1 derajat lintang ≈ 111.195 km -> 450 m ≈ 0.004047 derajat
        $d = (new GeoService())->distanceInMeters(self::CAMPUS_LAT, self::CAMPUS_LNG, self::CAMPUS_LAT + 0.004047, self::CAMPUS_LNG);

        $this->assertEqualsWithDelta(450, $d, 2);
        $this->assertLessThanOrEqual(500, $d);
    }

    public function test_point_550m_north_is_outside_500m_radius(): void
    {
        $d = (new GeoService())->distanceInMeters(self::CAMPUS_LAT, self::CAMPUS_LNG, self::CAMPUS_LAT + 0.004946, self::CAMPUS_LNG);

        $this->assertEqualsWithDelta(550, $d, 2);
        $this->assertGreaterThan(500, $d);
    }
}
