<?php
session_start();
header('Content-Type: application/json');
include '../db_connect.php';
include '../includes/geo.php';
if (!isAdmin()) { echo json_encode(['error' => 'Unauthorized']); exit(); }

$place = trim($_GET['place'] ?? '');
if ($place === '') { echo json_encode(['error' => 'place required']); exit(); }

$geo = geocode_city($place);
if (!$geo) { echo json_encode(['error' => "Couldn't find \"$place\""]); exit(); }

echo json_encode(['lat' => $geo['lat'], 'lng' => $geo['lng'], 'display_name' => $geo['display_name']]);
