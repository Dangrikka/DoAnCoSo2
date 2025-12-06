<?php
// admin/upload.php 
session_start();
header('Content-Type: application/json; charset=utf-8');

// === 1. KIỂM TRA QUYỀN ADMIN ===
include '../includes/auth.php';
check_role(['admin', 'staff']);

require_once '../config/database.php';
require_once '../controllers/SongController.php';

$songCtrl = new SongController();

// === 2. LẤY + VALIDATE DỮ LIỆU ===
$title  = trim($_POST['title'] ?? '');
$artist = trim($_POST['artist'] ?? '');

if ($title === '' || $artist === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tiêu đề và nghệ sĩ!']);
    exit;
}

// === 3. KIỂM TRA FILE ===
if (
    !isset($_FILES['audio']) || 
    $_FILES['audio']['error'] !== UPLOAD_ERR_OK || 
    $_FILES['audio']['size'] === 0
) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn file âm thanh hợp lệ!']);
    exit;
}

if (
    !isset($_FILES['image']) || 
    $_FILES['image']['error'] !== UPLOAD_ERR_OK || 
    $_FILES['image']['size'] === 0
) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ảnh bìa hợp lệ!']);
    exit;
}

$audioFile = $_FILES['audio'];
$imageFile = $_FILES['image'];

// === 4. KIỂM TRA MIME TYPE THẬT (CHỐNG HACK 100%) ===
$finfo = finfo_open(FILEINFO_MIME_TYPE);

$audioMime = finfo_file($finfo, $audioFile['tmp_name']);
$imageMime = finfo_file($finfo, $imageFile['tmp_name']);

finfo_close($finfo);

$allowedAudio = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/x-wav'];
$allowedImage = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

if (!in_array($audioMime, $allowedAudio)) {
    echo json_encode(['success' => false, 'message' => 'File âm thanh không hợp lệ! Chỉ chấp nhận MP3, WAV, OGG.']);
    exit;
}

if (!in_array($imageMime, $allowedImage)) {
    echo json_encode(['success' => false, 'message' => 'Ảnh bìa không hợp lệ! Chỉ chấp nhận JPG, PNG, WebP.']);
    exit;
}

// === 5. TẠO TÊN FILE DUY NHẤT + AN TOÀN ===
$audioExt = strtolower(pathinfo($audioFile['name'], PATHINFO_EXTENSION));
$imageExt = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));

// Tên file cực kỳ an toàn + không trùng
$audioName = 'song_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $audioExt;
$imageName = 'cover_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $imageExt;

$audioPath = "../assets/songs/audio/" . $audioName;
$imagePath = "../assets/songs/images/" . $imageName;

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// === 7. DI CHUYỂN FILE ===
if (!move_uploaded_file($audioFile['tmp_name'], $audioPath)) {
    echo json_encode(['success' => false, 'message' => 'Lỗi lưu file âm thanh!']);
    exit;
}

if (!move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
    unlink($audioPath); // Rollback
    echo json_encode(['success' => false, 'message' => 'Lỗi lưu ảnh bìa!']);
    exit;
}

// === 8. LƯU VÀO DATABASE ===
if ($songCtrl->store($title, $artist, $audioName, $imageName)) {
    echo json_encode([
        'success' => true,
        'message' => 'Upload bài hát thành công!',
        'data' => [
            'id'        => $songCtrl->getLastInsertedId ?? null,
            'title'     => $title,
            'artist'    => $artist,
            'audio_url' => "../assets/songs/audio/" . $audioName,
            'image_url' => "../assets/songs/images/" . $imageName
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    // Nếu lưu DB lỗi → xóa file vật lý
    @unlink($audioPath);
    @unlink($imagePath);
    echo json_encode(['success' => false, 'message' => 'Lỗi lưu vào cơ sở dữ liệu!']);
}
?>