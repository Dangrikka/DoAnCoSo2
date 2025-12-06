<?php
// api/song_detail.php – CHI TIẾT BÀI HÁT HOÀN HẢO NHẤT VIỆT NAM 2025
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Bắt buộc session để biết trạng thái yêu thích của user hiện tại
session_start();
$user_id = $_SESSION['user_id'] ?? 0;

require_once '../config/database.php';
require_once '../controllers/SongController.php';

try {
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Thiếu hoặc ID không hợp lệ'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $songCtrl = new SongController();
    $song = $songCtrl->show($id, $user_id); // Đã có is_favorite + play_count

    if (!$song || !is_array($song)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy bài hát'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // === XỬ LÝ ĐƯỜNG DẪN AN TOÀN + CHUẨN HÓA ===
    $audioFile = $song['audio_url'] ?? $song['audio_file'] ?? '';
    $imageFile = $song['image_url'] ?? $song['image'] ?? '';

    $response = [
        'id'          => (int)$song['id'],
        'title'       => $song['title'] ?? 'Không rõ',
        'artist'      => $song['artist'] ?? 'Nghệ sĩ không rõ',
        'audio_url'   => '../assets/songs/audio/' . basename($audioFile),     // ĐÚNG – chỉ tên file
        'image_url'   => !empty($imageFile)
            ? '../assets/songs/images/' . basename($imageFile)
            : '../assets/songs/images/default.jpg',
        'play_count'  => (int)($song['play_count'] ?? 0),
        'duration'    => $song['duration'] ?? null,
        'is_favorite' => (bool)($song['is_favorite'] ?? false),
        'created_at'  => $song['created_at'] ?? null,
        'updated_at'  => $song['updated_at'] ?? null
    ];

    echo json_encode([
        'success'   => true,
        'message'   => 'Lấy chi tiết bài hát thành công',
        'data'      => $response,
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success'   => false,
        'message'   => 'Lỗi hệ thống',
        'error'     => $e->getMessage(), // Chỉ bật khi dev
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}
?>