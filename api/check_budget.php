<?php
include '../db_connect.php';
$dest = strtolower(trim($_POST['destination'] ?? ''));
$result = mysqli_query($conn, 
  "SELECT avg_cost_per_day FROM destinations 
   WHERE LOWER(name) LIKE '%" . mysqli_real_escape_string($conn, $dest) . "%' 
   AND is_active=1 LIMIT 1"
);
$row = mysqli_fetch_assoc($result);
echo json_encode(['min_per_day' => $row ? (float)$row['avg_cost_per_day'] : 1000]);