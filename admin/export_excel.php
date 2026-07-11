<?php
session_start();
include '../db_connect.php';
if (!isAdmin()) { http_response_code(403); exit('Access denied'); }

$type = $_GET['type'] ?? 'users'; // 'users' or 'destinations'

if ($type === 'destinations') {
    $filename = 'wanderlust_destinations_' . date('Y-m-d') . '.csv';
    $result   = mysqli_query($conn, "SELECT id, name, country, category, mood_tags, best_months, avg_cost_per_day, is_active, created_at FROM destinations ORDER BY id ASC");
    $headers  = ['ID','Name','Country','Category','Mood Tags','Best Months','Avg Cost/Day (Rs.)','Active','Created At'];

    if (!$result) {
        die('Destinations query failed: ' . mysqli_error($conn));
    }

} else {
    $filename = 'wanderlust_users_' . date('Y-m-d') . '.csv';

    // Detect the primary key column name in users table
    $pk = 'id'; // fallback
    $desc = mysqli_query($conn, "DESCRIBE users");
    while ($col = mysqli_fetch_assoc($desc)) {
        if ($col['Key'] === 'PRI') {
            $pk = $col['Field'];
            break;
        }
    }

    $result = mysqli_query($conn,
        "SELECT u.$pk AS id, u.username, u.name, u.email, u.phone, u.role,
         (SELECT COUNT(*) FROM bookings WHERE user_id = u.$pk) AS trips,
         u.created_at
         FROM users u
         ORDER BY u.$pk ASC"
    );
    $headers = ['ID','Username','Full Name','Email','Phone','Role','Trips Planned','Joined At'];

    if (!$result) {
        die('Users query failed: ' . mysqli_error($conn));
    }
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM for Excel UTF-8
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');
fputcsv($out, $headers);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($out, array_values($row));
}
fclose($out);
exit();
?>