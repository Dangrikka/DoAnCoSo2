<?php
require_once '../includes/auth.php';
check_role(['admin', 'staff']);

require_once '../controllers/SongController.php';
$songCtrl = new SongController();

$msg = $msgType = '';

// Lấy ID bài hát
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $msg = "Bài hát không tồn tại!";
    $msgType = "danger";
    $song = null;
} else {
    $song = $songCtrl->show($id);
    if (!$song) {
        $msg = "Không tìm thấy bài hát!";
        $msgType = "danger";
    }
}

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $song) {
    $title  = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');

    if (empty($title) || empty($artist)) {
        $msg = "Vui lòng nhập đầy đủ tiêu đề và ca sĩ!";
        $msgType = "danger";
    } else {
        $audioNewName = $song['audio_url'];
        $imageNewName = $song['image_url'];

        // Xử lý upload file nhạc mới (nếu có)
        if (!empty($_FILES['audio_file']['name']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $audio = $_FILES['audio_file'];
            $ext = strtolower(pathinfo($audio['name'], PATHINFO_EXTENSION));
            $allowed = ['mp3', 'wav', 'ogg', 'm4a'];
            if (in_array($ext, $allowed) && $audio['size'] <= 30 * 1024 * 1024) {
                $audioNewName = uniqid('song_', true) . '.' . $ext;
                $dest = '../assets/songs/audio/' . $audioNewName;
                if (move_uploaded_file($audio['tmp_name'], $dest)) {
                    @unlink('../assets/songs/audio/' . $song['audio_url']); // xóa file cũ
                } else {
                    $msg = "Lỗi upload file nhạc!";
                    $msgType = "danger";
                    $audioNewName = $song['audio_url'];
                }
            } else {
                $msg = "File nhạc không hợp lệ hoặc quá lớn!";
                $msgType = "danger";
            }
        }

        // Xử lý ảnh bìa mới (nếu có)
        if (!empty($_FILES['image_file']['name']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $img = $_FILES['image_file'];
            $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($ext, $allowed) && $img['size'] <= 10 * 1024 * 1024) {
                $imageNewName = uniqid('cover_', true) . '.' . $ext;
                $dest = '../assets/songs/images/' . $imageNewName;
                if (move_uploaded_file($img['tmp_name'], $dest)) {
                    if ($song['image_url'] !== 'default.jpg') {
                        @unlink('../assets/songs/images/' . $song['image_url']);
                    }
                }
            }
        }

        // Cập nhật vào database
        if ($songCtrl->update($id, $title, $artist, $audioNewName, $imageNewName)) {
            $msg = "Cập nhật bài hát thành công!";
            $msgType = "success";
            $song = $songCtrl->show($id); // Cập nhật lại dữ liệu hiển thị
        } else {
            $msg = "Lỗi khi cập nhật database!";
            $msgType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Sửa bài hát - MusicVN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #00D4FF; --secondary: #9D4EDD; }
        body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); color: white; font-family: 'Inter', sans-serif; min-height: 100vh; padding: 30px 15px; }
        .container { max-width: 900px; margin: 0 auto; }
        .text-gradient { font-family: 'Poppins', sans-serif; font-size: 3.8rem; font-weight: 900; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-align: center; margin-bottom: 2rem; text-shadow: 0 0 50px rgba(0,212,255,0.5); }
        .card { background: rgba(20,20,40,0.95); border-radius: 28px; padding: 45px; box-shadow: 0 25px 80px rgba(0,212,255,0.35); border: 1px solid rgba(0,212,255,0.25); backdrop-filter: blur(16px); }
        .form-control { background: rgba(40,40,70,0.9); border: 1px solid #555; border-radius: 18px; padding: 18px 22px; color: white; font-size: 1.15rem; margin-bottom: 24px; width: 100%; transition: 0.4s; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 30px rgba(0,212,255,0.6); outline: none; }
        .file-input { background: rgba(40,40,70,0.8); border: 2px dashed #666; border-radius: 20px; padding: 40px; text-align: center; cursor: pointer; transition: 0.5s; margin-bottom: 28px; }
        .file-input:hover { border-color: var(--primary); background: rgba(0,212,255,0.15); }
        .file-input.dragover { border-color: var(--secondary); background: rgba(157,78,221,0.25); transform: scale(1.02); }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 20px 90px; border: none; border-radius: 60px; font-size: 1.5rem; font-weight: 800; cursor: pointer; transition: 0.5s; box-shadow: 0 15px 40px rgba(0,212,255,0.5); }
        .btn-primary:hover { transform: translateY(-8px) scale(1.05); box-shadow: 0 30px 70px rgba(0,212,255,0.8); }
        .alert { padding: 22px; border-radius: 18px; text-align: center; font-size: 1.4rem; font-weight: 700; margin: 30px 0; animation: fadeIn 0.6s; }
        .success { background: rgba(34,197,94,0.95); }
        .danger { background: rgba(239,68,68,0.95); }
        .preview-img { max-width: 250px; max-height: 250px; border-radius: 20px; margin-top: 20px; display: block; box-shadow: 0 15px 40px rgba(0,0,0,0.6); border: 4px solid var(--primary); }
        @keyframes fadeIn { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:none; } }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-gradient">SỬA BÀI HÁT</h1>

    <?php if ($msg): ?>
        <div class="alert <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($song): ?>
    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" class="form-control" placeholder="Tên bài hát" required 
                   value="<?= htmlspecialchars($song['title']) ?>">

            <input type="text" name="artist" class="form-control" placeholder="Tên ca sĩ / nhóm nhạc" required 
                   value="<?= htmlspecialchars($song['artist']) ?>">

            <!-- File nhạc hiện tại -->
            <div class="file-input">
                <i class="fas fa-music fa-5x mb-4" style="color:var(--primary)"></i>
                <p><strong>File hiện tại:</strong> <?= htmlspecialchars($song['audio_url']) ?></p>
                <small>Chỉ upload nếu muốn thay file nhạc mới (MP3, WAV, OGG, M4A • Max 30MB)</small>
                <input type="file" name="audio_file" accept="audio/*" style="display:none">
            </div>

            <!-- Ảnh bìa hiện tại -->
            <div class="file-input">
                <i class="fas fa-image fa-5x mb-4" style="color:var(--secondary)"></i>
                <p><strong>Ảnh bìa hiện tại:</strong></p>
                <img src="../assets/songs/images/<?= htmlspecialchars($song['image_url']) ?>" 
                     class="preview-img" onerror="this.src='../assets/songs/images/default.jpg'">
                <small class="d-block mt-3">Chỉ upload nếu muốn thay ảnh mới (JPG, PNG, WEBP • Max 10MB)</small>
                <input type="file" name="image_file" accept="image/*" style="display:none">
            </div>

            <div class="text-center">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> CẬP NHẬT BÀI HÁT
                </button>
                <a href="songs.php" style="color:#ccc; margin-left:40px; font-size:1.3rem; text-decoration:none;">
                    ← Quay lại danh sách
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.file-input').forEach(div => {
        div.addEventListener('click', () => div.querySelector('input[type=file]').click());
        div.addEventListener('dragover', e => { e.preventDefault(); div.classList.add('dragover'); });
        div.addEventListener('dragleave', () => div.classList.remove('dragover'));
        div.addEventListener('drop', e => {
            e.preventDefault();
            div.classList.remove('dragover');
            const file = e.dataTransfer.files[0];
            if (file) div.querySelector('input[type=file]').files = e.dataTransfer.files;
        });
    });
</script>

</body>
</html>