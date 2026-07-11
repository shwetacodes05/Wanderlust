<?php
/**
 * Geo helpers: geocode a city name to lat/lng, compute distance between
 * two points, and turn that distance into a rough but realistic transport
 * cost estimate. Used by api/transport_cost.php and admin/destinations.php.
 */

// Where geocoding results are cached on disk so we don't hammer the free
// Nominatim API (which rate-limits to ~1 request/second and will block
// abusive traffic). Cache never expires — city coordinates don't change.
define('GEOCODE_CACHE_DIR', __DIR__ . '/../cache/geocode');

/**
 * Look up latitude/longitude for a free-text place name (city, town,
 * even a small place like "Satara, Maharashtra") using OpenStreetMap's
 * Nominatim service. Returns ['lat'=>float,'lng'=>float,'display_name'=>str]
 * or null if it can't be found.
 */
function geocode_city(string $place): ?array {
    $place = trim($place);
    if ($place === '') return null;

    if (!is_dir(GEOCODE_CACHE_DIR)) {
        @mkdir(GEOCODE_CACHE_DIR, 0775, true);
    }
    $cacheKey  = md5(strtolower($place));
    $cacheFile = GEOCODE_CACHE_DIR . "/$cacheKey.json";

    if (is_file($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if ($cached) return $cached;
    }

    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'q'             => $place . ', India',
        'format'        => 'json',
        'limit'         => 1,
        'countrycodes'  => 'in',
        'addressdetails'=> 0,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        // Nominatim's usage policy requires a real identifying User-Agent
        // for every request or it will start rejecting them.
        CURLOPT_HTTPHEADER     => ['User-Agent: WanderLustApp/1.0 (contact@wanderlust.example)'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err || !$resp) return null;

    $rows = json_decode($resp, true);
    if (empty($rows) || !isset($rows[0]['lat'], $rows[0]['lon'])) return null;

    $result = [
        'lat'          => (float)$rows[0]['lat'],
        'lng'          => (float)$rows[0]['lon'],
        'display_name' => $rows[0]['display_name'] ?? $place,
    ];

    @file_put_contents($cacheFile, json_encode($result));
    return $result;
}

/**
 * Great-circle distance between two lat/lng points, in kilometers.
 */
function haversine_km(float $lat1, float $lng1, float $lat2, float $lng2): float {
    $R = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat / 2) ** 2
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}

/**
 * Rough round-trip, per-person transport cost estimate for a given
 * straight-line distance. Real fares vary a lot by route/season/demand —
 * this is a slab-based approximation, not a booking-grade fare, and the
 * result is labeled as an estimate in the UI.
 *
 * Straight-line (haversine) distance undercounts actual road/rail
 * distance, so we inflate it ~1.25x as a simple correction factor.
 */
function estimate_transport(float $distanceKm): array {
    $roadKm = $distanceKm * 1.25;

    if ($roadKm <= 100) {
        $mode = 'Bus / Shared Cab';
        $perKm = 3.5;
        $cost = max(300, $roadKm * $perKm * 2); // round trip
    } elseif ($roadKm <= 350) {
        $mode = 'Train (Sleeper/AC) or Bus';
        $perKm = 2.2;
        $cost = $roadKm * $perKm * 2;
    } elseif ($roadKm <= 900) {
        $mode = 'Train (AC) or Budget Flight';
        $perKm = 2.6;
        $cost = $roadKm * $perKm * 2;
    } else {
        $mode = 'Flight';
        // Flights aren't linear in distance: flat base fare + a per-km
        // component, one-way, then doubled for round trip.
        $oneWay = 2800 + ($roadKm * 2.8);
        $cost = $oneWay * 2;
    }

    return [
        'mode'          => $mode,
        'road_km'       => round($roadKm),
        'cost_per_person'=> (int)round($cost / 50) * 50, // round to nearest 50
    ];
}
