<?php
// ajax/get_user_albums.php – PHIÊN BẢN HOÀN HẢO NHẤT THẾ GIỚI 2025
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Chưa đăng nhập']);
    exit;
}

require_once '../config/database.php';

$user_id = (int)$_SESSION['user_id'];

// LẤY TẤT CẢ ALBUM CỦA USER + ĐẾM SỐ BÀI HÁT TRONG MỖI ALBUM (chỉ 1 query!)
$sql = "
    SELECT 
        a.id,
        a.name,
        a.cover_image,
        COUNT(als.song_id) AS song_count
    FROM albums a
    LEFT JOIN album_songs als ON a.id = als.album_id
    WHERE a.user_id = ?
    GROUP BY a.id, a.name, a.cover_image
    ORDER BY a.created_at DESC
";

$stmt = $db_conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$albums = [];
while ($row = $result->fetch_assoc()) {
    $albums[] = [
        'id'           => (int)$row['id'],
        'name'         => htmlspecialchars($row['name']),
        'cover_image'  => $row['cover_image'] 
            ? '../assets/albums/' . basename($row['cover_image']) 
            : '../assets/albums/default.jpg',
        'song_count'   => (int)$row['song_count']
    ];
}

$stmt->close();

echo json_encode([
    'status' => 'success',
    'albums' => $albums,
    'total'  => count($albums)
]);
?>