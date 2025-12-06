<?php
// api/songs.php – REST API HOÀN HẢO NHẤT VIỆT NAM 2025
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Bắt đầu session để biết user đã login chưa (yêu thích, play_count cá nhân hóa)
session_start();
$user_id = $_SESSION['user_id'] ?? 0;

require_once '../config/database.php';
require_once '../controllers/SongController.php';

$songCtrl = new SongController();

try {
    // Dùng hàm index() đã có sẵn – trả về đầy đủ is_favorite + play_count
    $songs = $songCtrl->index($user_id);

    if (!$songs || !is_array($songs)) {
        throw new Exception("Không có dữ liệu bài hát");
    }

    // Tối ưu dữ liệu trả về – chỉ gửi những gì frontend cần
    $response = array_map(function($song) {
        // Xử lý đường dẫn an toàn + chuẩn hóa tên cột
        $audioFile = $song['audio_url'] ?? $song['audio_file'] ?? '';
        $imageFile = $song['image_url'] ?? $song['image'] ?? '';

        return [
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
            'created_at'  => $song['created_at'] ?? null
        ];
    }, $songs);

    echo json_encode([
        'success'   => true,
        'message'   => 'Lấy danh sách bài hát thành công',
        'data'      => $response,
        'total'     => count($response),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống',
        'error'   => $e->getMessage(), // Bật khi dev, tắt khi production
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}
?>