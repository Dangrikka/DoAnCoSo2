<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn phải đăng nhập']);
    exit;
}

require_once '../config/database.php';
require_once '../controllers/SongController.php';

$song_id = isset($_POST['song_id']) ? (int)$_POST['song_id'] : 0;

if ($song_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID bài hát không hợp lệ']);
    exit;
}

$songCtrl = new SongController();

$res = $songCtrl->toggleFavorite($song_id, $_SESSION['user_id']);

if ($res === 'added') {
    echo json_encode(['status' => 'success', 'action' => 'added']);
} elseif ($res === 'removed') {
    echo json_encode(['status' => 'success', 'action' => 'removed']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Không thể xử lý yêu thích']);
}
exit;
