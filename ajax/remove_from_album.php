<?php
// ajax/remove_from_album.php – PHIÊN BẢN HOÀN HẢO NHẤT THẾ GIỚI 2025
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once '../config/database.php';

global $db_conn;
if (!$db_conn || $db_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối CSDL']);
    exit;
}

$album_id = (int)($_POST['album_id'] ?? 0);
$song_id  = (int)($_POST['song_id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if ($album_id <= 0 || $song_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// === KIỂM TRA ALBUM CÓ THUỘC VỀ USER KHÔNG (BẢO MẬT 100%) ===
$stmt = $db_conn->prepare("SELECT id FROM albums WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $album_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    echo json_encode(['status' => 'error', 'message' => 'Album không tồn tại hoặc không phải của bạn']);
    exit;
}
$stmt->close();

// === XÓA BÀI HÁT KHỎI ALBUM ===
$stmt = $db_conn->prepare("DELETE FROM album_songs WHERE album_id = ? AND song_id = ?");
$stmt->bind_param("ii", $album_id, $song_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        'status'  => 'success',
        'message' => 'Đã xóa bài hát khỏi album!'
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Không tìm thấy bài hát trong album hoặc đã bị xóa trước đó'
    ]);
}

$stmt->close();
$db_conn->close();
?>