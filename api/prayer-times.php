<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$city = clean_input($_GET['city'] ?? '');
$country = clean_input($_GET['country'] ?? '');
if (!$city || !$country) {
    echo json_encode(['error' => 'City and country required.']);
    exit;
}

$url = "https://api.aladhan.com/v1/timingsByCity?city=" . urlencode($city) . "&country=" . urlencode($country) . "&method=2";
$resp = @file_get_contents($url);
if (!$resp) { echo json_encode(['error' => 'API unavailable.']); exit; }

$json = json_decode($resp, true);
echo json_encode([
    'city' => $city,
    'country' => $country,
    'timings' => $json['data']['timings'] ?? null,
    'date' => $json['data']['date']['readable'] ?? null
]);
