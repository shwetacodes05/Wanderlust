<?php
header('Content-Type: application/json');
include '../db_connect.php';

$mood = mysqli_real_escape_string($conn, $_GET['mood'] ?? 'Adventure');
$result = mysqli_query($conn,
    "SELECT * FROM destinations WHERE mood_tags LIKE '%$mood%' AND is_active=1 ORDER BY RAND() LIMIT 4"
);
$destinations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $destinations[] = $row;
}
echo json_encode($destinations);
?>
