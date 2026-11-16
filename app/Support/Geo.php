<?php

namespace App\Support;

class Geo
{
    private const EARTH_RADIUS_MILES = 3958.8;

    /**
     * Great-circle distance between two points in miles.
     */
    public static function distanceMiles(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_MILES * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Rough lat/lng bounding box for a radius, used to narrow a query before exact distance checks.
     *
     * @return array{minLat: float, maxLat: float, minLng: float, maxLng: float}
     */
    public static function boundingBox(float $lat, float $lng, float $radiusMiles): array
    {
        $latDelta = $radiusMiles / 69.0;
        $lngDelta = $radiusMiles / max(1.0, 69.0 * cos(deg2rad($lat)));

        return [
            'minLat' => $lat - $latDelta,
            'maxLat' => $lat + $latDelta,
            'minLng' => $lng - $lngDelta,
            'maxLng' => $lng + $lngDelta,
        ];
    }
}
