<?php
// ajax/add_to_album.php – PHIÊN BẢN HOÀN HẢO 2025 – CHẠY MƯỢT NHƯ SPOTIFY!
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once '../config/database.php'; // <-- Đảm bảo file này có: $db_conn = new mysqli(...);

// Kết nối toàn cục (an toàn nhất)
global $db_conn;
if (!$db_conn || $db_conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối CSDL']);
    exit;
}

$song_id  = (int)($_POST['song_id'] ?? 0);
$album_id = (int)($_POST['album_id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if ($song_id <= 0 || $album_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

// 1. Kiểm tra album có thuộc về user không
$stmt = $db_conn->prepare("SELECT id FROM albums WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $album_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Album không tồn tại hoặc không phải của bạn']);
    exit;
}
$stmt->close();

// 2. Kiểm tra đã tồn tại trong album chưa
$stmt = $db_conn->prepare("SELECT id FROM album_songs WHERE album_id = ? AND song_id = ?");
$stmt->bind_param("ii", $album_id, $song_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['status' => 'exists', 'message' => 'Bài hát đã có trong album này rồi']);
    $stmt->close();
    exit;
}
$stmt->close();

// 3. THÊM VÀO ALBUM – DÙNG DEFAULT CURRENT_TIMESTAMP (không cần NOW())
$stmt = $db_conn->prepare("INSERT INTO album_songs (album_id, song_id) VALUES (?, ?)");
$stmt->bind_param("ii", $album_id, $song_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Đã thêm vào album thành công!'
    ]);
} else {
    // In lỗi chi tiết để debug (chỉ hiện khi dev)
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi thêm bài hát: ' . $stmt->error
    ]);
}

$stmt->close();
$db_conn->close();
?>