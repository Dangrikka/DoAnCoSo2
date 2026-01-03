<?php
// ajax/increment_play.php
session_start();
header('Content-Type: application/json; charset=utf-8');


// === BẮT BUỘC ĐĂNG NHẬP ===
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);
    exit;
}

require_once '../config/database.php';

// === KIỂM TRA KẾT NỐI DB ===
if (!isset($db_conn) || $db_conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection error'
    ]);
    exit;
}

// === VALIDATE INPUT ===
$song_id = isset($_POST['song_id']) ? (int)$_POST['song_id'] : 0;

if ($song_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid song_id'
    ]);
    exit;
}

// === TĂNG 1 LƯỢT NGHE DUY NHẤT ===
// YÊU CẦU: play_counts.song_id PHẢI LÀ UNIQUE KEY
$sql = "
    INSERT INTO play_counts (song_id, play_count, last_played)
    VALUES (?, 1, NOW())
    ON DUPLICATE KEY UPDATE
        play_count = play_count + 1,
        last_played = NOW()
";

$stmt = $db_conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Prepare failed'
    ]);
    exit;
}

$stmt->bind_param('i', $song_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Execute failed'
    ]);
}

$stmt->close();
$db_conn->close();
