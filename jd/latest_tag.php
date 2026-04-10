<?php
header('Content-Type: application/json');

$latestTagFile = "latest_tag.txt";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON from ESP32
    $data = json_decode(file_get_contents('php://input'), true);
    $tag = $data['tagid'] ?? '';

    if ($tag) {
        file_put_contents($latestTagFile, $tag); // save tag
        echo json_encode(['status' => 'success', 'tagid' => $tag]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No tag provided']);
        exit;
    }
}

// Default: return the latest tag
$tag = file_exists($latestTagFile) ? trim(file_get_contents($latestTagFile)) : '';
echo json_encode(['tagid' => $tag]);