<?php
// ajax/toggle_favorite.php – PHIÊN BẢN HOÀN HẢO NHẤT 2025
session_start();
header('Content-Type: application/json; charset=utf-8');

// === BẮT BUỘC ĐĂNG NHẬP ===
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once '../config/database.php';

// Dùng biến toàn cục an toàn
global $db_conn;
if (!$db_conn || $db_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối CSDL']);
    exit;
}

$song_id = (int)($_POST['song_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

if ($song_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID bài hát không hợp lệ']);
    exit;
}

// === KIỂM TRA BÀI HÁT CÓ TỒN TẠI KHÔNG (TÙY CHỌN NHƯNG NÊN CÓ ĐỂ TRÁNH LỖI) ===
$check_song = $db_conn->prepare("SELECT id FROM songs WHERE id = ? AND status = 'active'");
$check_song->bind_param("i", $song_id);
$check_song->execute();
if ($check_song->get_result()->num_rows === 0) {
    $check_song->close();
    echo json_encode(['status' => 'error', 'message' => 'Bài hát không tồn tại']);
    exit;
}
$check_song->close();

// === KIỂM TRA ĐÃ YÊU THÍCH CHƯA ===
$stmt = $db_conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND song_id = ?");
$stmt->bind_param("ii", $user_id, $song_id);
$stmt->execute();
$result = $stmt->get_result();
$is_favorite = $result->num_rows > 0;
$stmt->close();

if ($is_favorite) {
    // === BỎ YÊU THÍCH ===
    $stmt = $db_conn->prepare("DELETE FROM favorites WHERE user_id = ? AND song_id = ?");
    $stmt->bind_param("ii", $user_id, $song_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'status' => 'removed',
            'message' => 'Đã xóa khỏi yêu thích'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi bỏ yêu thích']);
    }
} else {
    // === THÊM VÀO YÊU THÍCH ===
    $stmt = $db_conn->prepare("INSERT INTO favorites (user_id, song_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $song_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'added',
            'message' => 'Đã thêm vào yêu thích!'
        ]);
    } else {
        // Tránh trùng (nếu có lỗi UNIQUE KEY)
        echo json_encode(['status' => 'error', 'message' => 'Bài hát đã có trong yêu thích']);
    }
}

$stmt->close();
$db_conn->close();
?>