<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Chưa đăng nhập"]);
    exit;
}

require_once "../config/database.php";
require_once "../controllers/AlbumController.php";

$albumCtrl = new AlbumController();

$album_id = intval($_POST['album_id'] ?? 0);
$song_id  = intval($_POST['song_id'] ?? 0);
$user_id  = intval($_SESSION['user_id']);

if ($album_id <= 0 || $song_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

$album = $albumCtrl->getAlbumById($album_id, $user_id);

if (!$album) {
    echo json_encode(["status" => "error", "message" => "Album không tồn tại hoặc không phải của bạn"]);
    exit;
}

if (!$albumCtrl->addSong($album_id, $song_id)) {
    echo json_encode(["status" => "exists", "message" => "Bài hát đã tồn tại"]);
    exit;
}

echo json_encode(["status" => "success", "message" => "Đã thêm bài hát vào album"]);
?>
