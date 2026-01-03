<?php
// 1. Khởi động session & Check quyền
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../controllers/SongController.php';

check_role(['admin', 'staff']);

// 2. Lấy ID từ URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$id = (int)$_GET['id'];
$songCtrl = new SongController();

// 3. Lấy thông tin bài hát hiện tại
$song = $songCtrl->getSongById($id); // Giả sử bạn có hàm này trong Controller
if (!$song) {
    die("Bài hát không tồn tại!");
}

$msg = '';
$msgType = '';

// 4. XỬ LÝ KHI SUBMIT FORM (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    
    // Mặc định giữ nguyên file cũ
    $audioName = $song['file_path']; // Tên cột trong DB chứa file nhạc
    $imageName = $song['image'];     // Tên cột trong DB chứa ảnh

    // Upload nhạc mới (nếu có chọn)
    if (isset($_FILES['audio']) && $_FILES['audio']['error'] === 0) {
        // Logic upload file nhạc (Giản lược: bạn nên move_uploaded_file vào folder)
        $audioName = time() . '_' . $_FILES['audio']['name'];
        move_uploaded_file($_FILES['audio']['tmp_name'], "../assets/songs/audio/" . $audioName);
    }

    // Upload ảnh mới (nếu có chọn)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/songs/images/" . $imageName);
    }

    // Gọi hàm Update trong Controller
    // Hàm update cần nhận: ID, Title, Artist, AudioFile, ImageFile
    if ($songCtrl->update($id, $title, $artist, $audioName, $imageName)) {
        $msg = "Cập nhật bài hát thành công!";
        $msgType = "success";
        // Cập nhật lại biến $song để hiển thị dữ liệu mới ngay lập tức
        $song = $songCtrl->getSongById($id); 
    } else {
        $msg = "Lỗi khi cập nhật cơ sở dữ liệu!";
        $msgType = "error";
    }
}

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin • Chỉnh sửa bài hát</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

<style>
    /* --- GIỮ NGUYÊN CSS NHƯ TRANG UPLOAD --- */
    body {
        background: #0d0f2d;
        font-family: 'Inter', sans-serif;
        padding: 40px;
        color: #fff;
    }
    .edit-container {
        max-width: 650px;
        margin: auto;
        background: rgba(20,20,40,0.95);
        padding: 35px;
        border-radius: 25px;
        box-shadow: 0 15px 60px rgba(243, 156, 18, 0.15); /* Màu cam nhẹ cho trang edit */
        border: 1px solid rgba(255,255,255,0.05);
    }

    /* Header & Back Button */
    .header-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    .btn-back {
        text-decoration: none;
        color: #a0a0b0;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255,255,255,0.05);
        border-radius: 50px;
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }
    .btn-back:hover {
        background: rgba(243, 156, 18, 0.2);
        border-color: #f39c12;
        color: #f39c12;
        transform: translateX(-5px);
    }
    .title { margin: 0; font-size: 1.8rem; color: #f39c12; text-shadow: 0 0 20px rgba(243,156,18,0.3); }

    /* Form Elements */
    .form-control {
        width: 100%;
        padding: 15px;
        font-size: 1.1rem;
        border-radius: 14px;
        border: 1px solid #555;
        background: rgba(40,40,60,0.85);
        color: #fff;
        margin-bottom: 20px;
        box-sizing: border-box;
    }
    .form-control:focus { outline: none; border-color: #f39c12; }

    /* Dropzones */
    .dropzone {
        border: 2px dashed #666;
        padding: 25px;
        text-align: center;
        border-radius: 20px;
        cursor: pointer;
        margin-bottom: 20px;
        transition: .3s;
        background: rgba(0,0,0,0.2);
        position: relative;
    }
    .dropzone:hover { background: rgba(255,255,255,0.05); border-color: #f39c12; }
    
    /* Current File Info */
    .current-info {
        text-align: left;
        font-size: 0.9rem;
        color: #aaa;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .current-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #555;
    }
    .preview-img-large {
        max-width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
        margin-top: 10px;
        border: 2px solid #f39c12;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    /* Submit Button */
    .btn-save {
        width: 100%;
        padding: 16px;
        background: linear-gradient(45deg, #f39c12, #d35400);
        border: none;
        border-radius: 16px;
        font-size: 1.4rem;
        font-weight: 700;
        cursor: pointer;
        color: white;
        transition: .3s;
        margin-top: 10px;
    }
    .btn-save:hover { transform: scale(1.02); box-shadow: 0 10px 30px rgba(243,156,18,0.4); }

    /* Messages */
    .msg { margin-bottom: 20px; padding: 15px; border-radius: 12px; text-align: center; font-weight: bold; }
    .success { background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; color: #2ecc71; }
    .error { background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #e74c3c; }
</style>
</head>
<body>

<div class="edit-container">

    <div class="header-nav">
        <a href="songs.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Hủy / Quay lại
        </a>
        <h2 class="title"><i class="fa-solid fa-pen-to-square"></i> Sửa Bài Hát</h2>
        <div style="width: 80px;"></div>
    </div>

    <?php if ($msg): ?>
        <div class="msg <?= $msgType ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        
        <label style="color: #ccc; margin-left: 5px;">Tên bài hát</label>
        <input type="text" name="title" class="form-control" 
               value="<?= htmlspecialchars($song['title']) ?>" required>

        <label style="color: #ccc; margin-left: 5px;">Ca sĩ / Nhóm nhạc</label>
        <input type="text" name="artist" class="form-control" 
               value="<?= htmlspecialchars($song['artist']) ?>" required>

        <div style="margin-top: 30px;">
            <div class="current-info">
                <i class="fa-solid fa-music"></i> 
                File hiện tại: <span style="color:#fff"><?= htmlspecialchars($song['file_path']) ?></span>
            </div>
            
            <div class="dropzone" id="audioZone">
                <i class="fa-solid fa-file-audio fa-2x" style="color:#f39c12; margin-bottom:10px"></i>
                <p style="margin:0; font-size:0.9rem">Bấm vào đây để chọn file mới (Nếu muốn thay đổi)</p>
                <input type="file" name="audio" id="audio" accept="audio/*" style="display:none">
                <p id="audioNameNew" style="color: #f39c12; margin-top: 5px; font-weight: bold;"></p>
            </div>
        </div>

        <div style="margin-top: 20px;">
            <div class="current-info">
                <i class="fa-solid fa-image"></i> Ảnh bìa hiện tại
            </div>

            <div class="dropzone" id="imageZone">
                <img id="previewImg" 
                     src="../assets/songs/images/<?= htmlspecialchars($song['image']) ?>" 
                     class="preview-img-large"
                     onerror="this.src='../assets/songs/images/default.jpg'">
                
                <p style="margin-top:15px; color:#aaa; font-size:0.9rem">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Bấm vào ảnh để thay đổi
                </p>
                <input type="file" name="image" id="image" accept="image/*" style="display:none">
            </div>
        </div>

        <button type="submit" class="btn-save">
            <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
        </button>

    </form>
</div>

<script>
    /* 1. Xử lý click và chọn file Audio */
    const audioZone = document.getElementById('audioZone');
    const audioInput = document.getElementById('audio');
    const audioNameDisplay = document.getElementById('audioNameNew');

    audioZone.addEventListener('click', () => audioInput.click());
    
    audioInput.addEventListener('change', () => {
        if (audioInput.files && audioInput.files[0]) {
            audioNameDisplay.innerText = "Đã chọn: " + audioInput.files[0].name;
            audioZone.style.borderColor = "#f39c12";
            audioZone.style.background = "rgba(243, 156, 18, 0.1)";
        }
    });

    /* 2. Xử lý click và Preview ảnh mới */
    const imageZone = document.getElementById('imageZone');
    const imageInput = document.getElementById('image');
    const previewImg = document.getElementById('previewImg');

    imageZone.addEventListener('click', () => imageInput.click());

    imageInput.addEventListener('change', () => {
        if (imageInput.files && imageInput.files[0]) {
            // Tạo URL ảo để xem trước ảnh mà không cần upload
            const newUrl = URL.createObjectURL(imageInput.files[0]);
            previewImg.src = newUrl;
        }
    });
</script>

</body>
</html>