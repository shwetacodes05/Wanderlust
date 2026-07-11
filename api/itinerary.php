<?php
session_start();
header('Content-Type: application/json');
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']); exit();
}

$destination = htmlspecialchars($_POST['destination'] ?? '');
$days        = (int)($_POST['days'] ?? 3);
$budget      = (int)($_POST['budget'] ?? 10000);
$travelers   = (int)($_POST['travelers'] ?? 2);
$interests   = htmlspecialchars($_POST['interests'] ?? 'sightseeing, food');

if (empty($destination)) {
    echo json_encode(['error' => 'Destination required']); exit();
}

// Pull real data for this destination from our own DB so the AI is grounded
// instead of guessing prices/details out of thin air.
$dest_row = null;
$dest_esc = mysqli_real_escape_string($conn, $destination);
$dest_result = mysqli_query($conn,
    "SELECT name, description, category, mood_tags, best_months, avg_cost_per_day
     FROM destinations
     WHERE LOWER(name) LIKE LOWER('%$dest_esc%') AND is_active = 1
     LIMIT 1"
);
if ($dest_result && mysqli_num_rows($dest_result) > 0) {
    $dest_row = mysqli_fetch_assoc($dest_result);
}

// Budget math: what the site itself would predict this trip should cost.
$per_day_budget = round($budget / max($days, 1) / max($travelers, 1));

$context_block = '';
if ($dest_row) {
    $known_daily = (int)$dest_row['avg_cost_per_day'];
    $context_block = "Known facts about {$dest_row['name']} (use these, do not contradict them):
- Category: {$dest_row['category']}
- Known for: {$dest_row['description']}
- Typical mood/fit: {$dest_row['mood_tags']}
- Best months to visit: {$dest_row['best_months']}
- Our site's baseline average cost: Rs.{$known_daily} per person per day (use this as your anchor, not a random guess)
";
} else {
    $context_block = "Note: this destination is not in our curated database, so rely on well-established real-world knowledge of {$destination} only. Do not invent landmarks or prices that don't exist.
";
}

$prompt = "Create a detailed {$days}-day travel itinerary for {$destination}, India.

{$context_block}
Trip details:
- Total budget: Rs.{$budget} for {$travelers} traveler(s) for the whole trip
- That works out to roughly Rs.{$per_day_budget} per person per day — your 'Estimated cost' lines MUST add up close to this per-day figure (within about 15%). If the stated budget is unrealistically low or high for this destination, say so explicitly in one line instead of silently inventing numbers.
- Interests: {$interests}

Rules:
1. Use only real, verifiable places (actual named neighborhoods, beaches, forts, temples, markets, restaurants) — no generic placeholders like 'a local market' or 'a scenic spot'.
2. Every cost you give must be plausible for India in 2026 and consistent with the per-day budget above.
3. Do not repeat the same activity type every day.

Format your response as a clean day-by-day plan:
Day 1: [Title]
  Morning: [Activity + tip]
  Afternoon: [Activity + tip]
  Evening: [Activity + tip]
  Estimated cost: Rs.XXXX

Include best local food, one hidden gem per day, transport tips. Keep it exciting but keep every fact and price grounded in reality.";

$api_key = defined('GROQ_API_KEY') ? GROQ_API_KEY : '';

if (empty($api_key)) {
    echo json_encode(['error' => 'GROQ_API_KEY not set in db_connect.php']); exit();
}

$body = json_encode([
    'model'    => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => 'You are an expert Indian travel planner with deep, accurate, up-to-date knowledge of Indian destinations. Give detailed, practical, realistic itineraries. Never invent places or prices — if unsure, stick to well-known facts.'],
        ['role' => 'user',   'content' => $prompt]
    ],
    'max_tokens'  => 2200,
    'temperature' => 0.45
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);
$response = curl_exec($ch);
$err      = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['error' => 'cURL failed: ' . $err]); exit();
}

$data = json_decode($response, true);
$text = $data['choices'][0]['message']['content'] ?? '';

if (empty($text)) {
    $api_error = $data['error']['message'] ?? 'Unknown error';
    echo json_encode(['error' => 'Groq error: ' . $api_error]); exit();
}

// Save to DB if user logged in
if (isset($_SESSION['user_id'])) {
    $uid  = (int)$_SESSION['user_id'];
    $dest = mysqli_real_escape_string($conn, $destination);
    $it   = mysqli_real_escape_string($conn, $text);
    $bgt  = (float)$budget;
    mysqli_query($conn,
        "INSERT INTO bookings (user_id, trip_name, budget, itinerary, status)
         VALUES ($uid, '$dest', $bgt, '$it', 'planned')"
    );
}

echo json_encode(['itinerary' => $text]);
?>