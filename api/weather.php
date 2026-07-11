<?php
header('Content-Type: application/json');
include '../db_connect.php';

$city = urlencode($_GET['city'] ?? 'Mumbai');
$key  = WEATHER_API_KEY;

if ($key === 'YOUR_OPENWEATHERMAP_KEY_HERE') {
    // Demo data
    echo json_encode([
        'name'    => urldecode($city),
        'temp'    => 28,
        'feels'   => 30,
        'desc'    => 'Partly Cloudy',
        'icon'    => '02d',
        'humidity'=> 72,
        'wind'    => 14,
        'demo'    => true,
        'best_months' => 'October to February is ideal for most Indian destinations.',
        'message' => 'Add OpenWeatherMap API key in db_connect.php for live weather.'
    ]);
    exit();
}

$url = "https://api.openweathermap.org/data/2.5/weather?q={$city},IN&appid={$key}&units=metric";
$ch  = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$res = curl_exec($ch);
curl_close($ch);

$data = json_decode($res, true);
if (isset($data['cod']) && $data['cod'] == 200) {
    echo json_encode([
        'name'     => $data['name'],
        'temp'     => round($data['main']['temp']),
        'feels'    => round($data['main']['feels_like']),
        'desc'     => $data['weather'][0]['description'],
        'icon'     => $data['weather'][0]['icon'],
        'humidity' => $data['main']['humidity'],
        'wind'     => round($data['wind']['speed'] * 3.6),
    ]);
} else {
    echo json_encode(['error' => 'City not found. Try a different name.']);
}
?>
