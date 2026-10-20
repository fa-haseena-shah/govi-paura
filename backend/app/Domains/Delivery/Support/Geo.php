<?php

namespace App\Domains\Delivery\Support;

final class Geo
{
    private const EARTH_RADIUS_KM = 6371.0088;

    /** Great-circle distance between two points, in kilometres. */
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(min(1.0, sqrt($a)));
    }

    /**
     * Rough road distance (km) and driving time (minutes) between two points, or [null, null]
     * when either point is missing. Straight line x road_factor, at avg_speed_kmh.
     *
     * @return array{0: ?float, 1: ?int}
     */
    public static function estimate(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): array
    {
        if ($lat1 === null || $lng1 === null || $lat2 === null || $lng2 === null) {
            return [null, null];
        }

        $km = self::haversineKm($lat1, $lng1, $lat2, $lng2) * (float) config('delivery.road_factor');
        $speed = max(1.0, (float) config('delivery.avg_speed_kmh'));

        return [round($km, 2), (int) ceil($km / $speed * 60)];
    }
}
