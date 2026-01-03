<?php
require_once '../includes/auth.php';
check_role(['admin', 'staff']);

require_once '../config/database.php';
require_once '../controllers/SongController.php';

/* =========================
   XỬ LÝ AJAX UPLOAD (POST)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    try {
        if (empty($_POST['title']) || empty($_POST['artist'])) {
            throw new Exception('Thiếu tiêu đề hoặc ca sĩ');
        }

        if (!isset($_FILES['audio'], $_FILES['image'])) {
            throw new Exception('Thiếu file upload');
        }

        $title  = trim($_POST['title']);
        $artist = trim($_POST['artist']);

        // AUDIO
        $audioName = time() . '_' . basename($_FILES['audio']['name']);
        $audioPath = '../assets/songs/audio/' . $audioName;
        if (!move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath)) {
            throw new Exception('Upload nhạc thất bại');
        }

        // IMAGE
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $imagePath = '../assets/songs/images/' . $imageName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            throw new Exception('Upload ảnh thất bại');
        }

        // DB
        $songCtrl = new SongController();
        if (!$songCtrl->store($title, $artist, $audioName, $imageName)) {
            throw new Exception('Lỗi lưu database');
        }

        $response['success'] = true;
        $response['message'] = 'Upload thành công';

    } catch (Exception $e) {
        http_response_code(400);
        $response['message'] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

include '../includes/header.php';
?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin • Upload bài hát</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

<style>
    body {
        background: #0d0f2d;
        font-family: 'Inter', sans-serif;
        padding: 40px;
        color: #fff;
    }
    .upload-container {
        max-width: 650px;
        margin: auto;
        background: rgba(20,20,40,0.9);
        padding: 35px;
        border-radius: 25px;
        box-shadow: 0 15px 60px rgba(0,212,255,0.25);
        position: relative; /* Để định vị các thành phần con nếu cần */
    }
    
    /* --- CSS CHO NÚT QUAY LẠI --- */
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
        background: rgba(0, 212, 255, 0.15);
        border-color: #00D4FF;
        color: white;
        transform: translateX(-5px); /* Hiệu ứng trượt sang trái */
    }
    
    .title {
        margin: 0; /* Reset margin để căn chỉnh đẹp hơn */
        font-size: 1.8rem;
    }

    /* --- CÁC CSS CŨ GIỮ NGUYÊN --- */
    .form-control {
        width: 100%;
        padding: 15px;
        font-size: 1.1rem;
        border-radius: 14px;
        border: 1px solid #555;
        background: rgba(40,40,60,0.85);
        color: #fff;
        margin-bottom: 20px;
        box-sizing: border-box; /* Fix lỗi tràn width */
    }
    .dropzone {
        border: 2px dashed #00D4FF;
        padding: 35px;
        text-align: center;
        border-radius: 20px;
        cursor: pointer;
        margin-bottom: 20px;
        transition: .3s;
    }
    .dropzone:hover {
        background: rgba(0,212,255,0.15);
    }
    .dropzone.dragover {
        background: rgba(157,78,221,0.30);
        border-color: #9D4EDD;
    }
    .preview-img {
        max-width: 180px;
        border-radius: 16px;
        margin-top: 15px;
        display: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.5);
    }
    .btn-upload {
        width: 100%;
        padding: 16px;
        background: linear-gradient(45deg, #00D4FF, #9D4EDD);
        border: none;
        border-radius: 16px;
        font-size: 1.4rem;
        font-weight: 700;
        cursor: pointer;
        color: white;
        transition: .3s;
    }
    .btn-upload:hover {
        transform: scale(1.03);
        box-shadow: 0 10px 35px rgba(0,212,255,0.6);
    }
    .msg {
        margin-top: 20px;
        padding: 14px;
        border-radius: 10px;
        text-align: center;
        font-weight: 600;
        display: none;
    }
    .success { background: rgba(34,197,94,0.9); }
    .error { background: rgba(239,68,68,0.9); }
</style>

</head>
<body>

<div class="upload-container">

    <div class="header-nav">
        <a href="songs.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Quay lại DS
        </a>
        <h2 class="title"><i class="fa-solid fa-cloud-upload-alt"></i> Upload Bài Hát</h2>
        <div style="width: 100px;"></div> </div>

    <input type="text" id="title" class="form-control" placeholder="Tên bài hát">

    <input type="text" id="artist" class="form-control" placeholder="Tên ca sĩ / nhóm nhạc">

    <div class="dropzone" id="audioZone">
        <i class="fa-solid fa-music fa-3x mb-3" style="color:#00D4FF"></i>
        <p>Nhấp hoặc kéo thả file nhạc (MP3, WAV, OGG)</p>
        <input type="file" id="audio" accept="audio/*" style="display:none">
    </div>

    <div class="dropzone" id="imageZone">
        <i class="fa-solid fa-image fa-3x mb-3" style="color:#9D4EDD"></i>
        <p>Nhấp hoặc kéo thả ảnh bìa (JPG/PNG/WebP)</p>
        <input type="file" id="image" accept="image/*" style="display:none">
        <img id="previewImg" class="preview-img">
    </div>

    <button class="btn-upload" onclick="uploadSong()">
        <i class="fa-solid fa-cloud-arrow-up"></i> Tải Lên
    </button>

    <div id="msgBox" class="msg"></div>

</div>

<link rel="stylesheet" href="../assets/css/style.css">

<script>
/* ===========================
   XỬ LÝ DROPZONE CHUNG
=========================== */
function setupDropzone(zoneId, inputId, preview = null) {
    const zone = document.getElementById(zoneId);
    const input = document.getElementById(inputId);

    zone.addEventListener("click", () => input.click());

    zone.addEventListener("dragover", e => {
        e.preventDefault();
        zone.classList.add("dragover");
    });

    zone.addEventListener("dragleave", () => {
        zone.classList.remove("dragover");
    });

    zone.addEventListener("drop", e => {
        e.preventDefault();
        zone.classList.remove("dragover");

        let file = e.dataTransfer.files[0];
        input.files = e.dataTransfer.files;

        if (preview && file.type.startsWith("image")) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = "block";
        }
    });

    input.addEventListener("change", () => {
        if (preview && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
            preview.style.display = "block";
        }
    });
}

setupDropzone("audioZone", "audio");
setupDropzone("imageZone", "image", document.getElementById("previewImg"));

/* ===========================
   GỬI FORM QUA AJAX
=========================== */
function uploadSong() {

    let title = document.getElementById("title").value.trim();
    let artist = document.getElementById("artist").value.trim();
    let audio = document.getElementById("audio").files[0];
    let image = document.getElementById("image").files[0];
    let msgBox = document.getElementById("msgBox");

    if (!title || !artist) {
        msgBox.innerText = "Vui lòng nhập tiêu đề và ca sĩ!";
        msgBox.className = "msg error";
        msgBox.style.display = "block";
        return;
    }

    if (!audio || !image) {
        msgBox.innerText = "Vui lòng chọn file nhạc và ảnh bìa!";
        msgBox.className = "msg error";
        msgBox.style.display = "block";
        return;
    }

    let formData = new FormData();
    formData.append("title", title);
    formData.append("artist", artist);
    formData.append("audio", audio);
    formData.append("image", image);

    fetch("upload.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        msgBox.style.display = "block";
        if (data.success) {
            msgBox.className = "msg success";
            msgBox.innerText = "✔ Upload thành công!";
            // Reset form sau khi upload thành công (tùy chọn)
            setTimeout(() => { window.location.href = "songs.php"; }, 1500); // Tự động quay về list sau 1.5s
        } else {
            msgBox.className = "msg error";
            msgBox.innerText = "✖ " + data.message;
        }
    })
    .catch(err => {
        msgBox.style.display = "block";
        msgBox.className = "msg error";
        msgBox.innerText = "Lỗi kết nối server!";
    });
}
</script>

</body>
</html>