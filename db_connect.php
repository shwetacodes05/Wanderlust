<?php

// Real secrets live in config.php, which is NOT committed to git (see
// .gitignore). This file defines DB_HOST, DB_USER, DB_PASS, DB_NAME,
// GROQ_API_KEY, WEATHER_API_KEY. Copy config.sample.php to config.php
// and fill in your real values.
$__config = __DIR__ . '/config.php';
if (!file_exists($__config)) {
    die('Missing config.php — copy config.sample.php to config.php and fill in your credentials.');
}
require $__config;

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die(json_encode(['error' => 'DB connection failed: ' . mysqli_connect_error()]));
}
mysqli_set_charset($conn, 'utf8mb4');

// Session helper
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function redirect($url) {
    header("Location: $url"); exit();
}
?>