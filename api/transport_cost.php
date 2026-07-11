<?php
header('Content-Type: application/json');
include '../db_connect.php';
include '../includes/geo.php';

$origin   = trim($_POST['origin'] ?? $_GET['origin'] ?? '');
$destId   = (int)($_POST['dest_id'] ?? $_GET['dest_id'] ?? 0);

if ($origin === '' || $destId <= 0) {
    echo json_encode(['error' => 'origin and dest_id are required']);
    exit();
}

$destRes = mysqli_query($conn, "SELECT id, name, latitude, longitude FROM destinations WHERE id=$destId LIMIT 1");
$dest = $destRes ? mysqli_fetch_assoc($destRes) : null;

if (!$dest) {
    echo json_encode(['error' => 'Destination not found']);
    exit();
}

if ($dest['latitude'] === null || $dest['longitude'] === null) {
    // This destination was never given coordinates (e.g. added via admin
    // panel before the geo fields existed). Fail gracefully instead of a
    // wrong distance.
    echo json_encode(['error' => 'This destination has no coordinates set yet. Add them from the admin panel.']);
    exit();
}

$originGeo = geocode_city($origin);
if (!$originGeo) {
    echo json_encode(['error' => "Couldn't locate \"$origin\". Try a more specific city name."]);
    exit();
}

$distanceKm = haversine_km(
    $originGeo['lat'], $originGeo['lng'],
    (float)$dest['latitude'], (float)$dest['longitude']
);

$estimate = estimate_transport($distanceKm);

echo json_encode([
    'origin'          => $originGeo['display_name'],
    'destination'     => $dest['name'],
    'straight_line_km'=> round($distanceKm),
    'road_km'         => $estimate['road_km'],
    'mode'            => $estimate['mode'],
    'transport_cost_per_person' => $estimate['cost_per_person'],
]);
