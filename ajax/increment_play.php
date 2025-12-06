<?php
// ajax/increment_play.php – PHIÊN BẢN HOÀN HẢO NHẤT 2025
session_start();
header('Content-Type: application/json; charset=utf-8');

// === BẮT BUỘC PHẢI ĐĂNG NHẬP ĐỂ TĂNG LƯỢT NGHE (ngăn spam) ===
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

// Kiểm tra kết nối
if (!$db_conn || $db_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit;
}

$song_id = (int)($_POST['song_id'] ?? 0);

if ($song_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid song_id']);
    exit;
}


$stmt = $db_conn->prepare("
    INSERT INTO play_counts (song_id, play_count, last_played) 
    VALUES (?, 1, NOW()) 
    ON DUPLICATE KEY UPDATE 
        play_count = play_count + 1,
        last_played = NOW()
");

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $db_conn->error]);
    exit;
}

$stmt->bind_param("i", $song_id);

if ($stmt->execute()) {
    // === TÙY CHỌN: CẬP NHẬT CỘT play_count TRONG BẢNG songs (nếu bạn có cột này) ===
    $db_conn->query("UPDATE songs SET play_count = play_count + 1 WHERE id = $song_id");
    
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$db_conn->close();
?>