<?php
$q = $_GET['q'] ?? '';
if (strlen($q) < 1) {
    echo json_encode(["success" => true, "data" => []]);
    exit;
}

$songCtrl = new SongController();
$results = $songCtrl->searchSongs($q);

echo json_encode([
    "success" => true,
    "query" => $q,
    "data" => $results,
    "total" => count($results)
], JSON_UNESCAPED_UNICODE);