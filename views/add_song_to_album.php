<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn phải đăng nhập.");
}

$album_id = (int)($_GET['album_id'] ?? 0);
$song_id  = (int)($_GET['song_id'] ?? 0);

if ($album_id <= 0 || $song_id <= 0) {
    die("Dữ liệu không hợp lệ.");
}

require_once '../controllers/AlbumController.php';
$albumCtrl = new AlbumController();

// ————————————————
// 1. Kiểm tra album có thuộc user không
// ————————————————
$album = $albumCtrl->getAlbumById($album_id, $_SESSION['user_id']);
if (!$album) {
    die("Bạn không có quyền thêm bài hát vào album này.");
}

// ————————————————
// 2. Thêm bài hát vào album
// ————————————————
$result = $albumCtrl->addSong($album_id, $song_id);

if ($result === false) {
    die("Lỗi thêm bài hát: Bài hát đã tồn tại trong album.");
}

if ($result === true) {
    header("Location: song_detail.php?id={$song_id}&added=1");
    exit;
}

// Trường hợp lỗi SQL — trả về nội dung lỗi
die("Lỗi thêm bài hát: " . $result);
