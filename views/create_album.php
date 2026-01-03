<?php
// create_album.php – OPTIMIZED & SECURE 2025

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../controllers/AlbumController.php';

$albumCtrl = new AlbumController();
$message = '';

$uploadDir = "../assets/albums/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// XỬ LÝ FORM SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $coverImage = "default.jpg";

    if (empty($name)) {
        $message = '<div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>Vui lòng nhập tên album!</div>';
    } elseif (strlen($name) > 100) {
        $message = '<div class="alert alert-danger shadow-sm">Tên album quá dài (tối đa 100 ký tự)!</div>';
    } else {
        // XỬ LÝ UPLOAD ẢNH (Bảo mật hơn với check MIME Type)
        if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            
            $file = $_FILES['cover_image'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Check MIME Type thực tế
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                $message = '<div class="alert alert-danger">File không hợp lệ! Chỉ chấp nhận JPG, PNG, GIF.</div>';
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $message = '<div class="alert alert-danger">Dung lượng ảnh quá lớn (Tối đa 5MB)!</div>';
            } else {
                $fileName = time() . "_" . uniqid() . "." . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) {
                    $coverImage = $fileName;
                } else {
                    $message = '<div class="alert alert-danger">Lỗi upload ảnh. Vui lòng thử lại!</div>';
                }
            }
        }

        if (empty($message)) {
            $albumId = $albumCtrl->create($_SESSION['user_id'], $name, $coverImage);
            if ($albumId) {
                header("Location: my_albums.php?created=1");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Lỗi cơ sở dữ liệu. Vui lòng thử lại sau!</div>';
            }
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <div class="card border-0 shadow-2xl rounded-4 overflow-hidden card-hover-effect">
                    
                    <div class="card-header bg-gradient-primary text-center py-5 position-relative border-0">
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-pattern opacity-10"></div>
                        <div class="position-relative z-1">
                            <div class="icon-circle mb-3 mx-auto shadow-lg">
                                <i class="fas fa-compact-disc fa-3x text-white fa-spin-slow"></i>
                            </div>
                            <h2 class="fw-bold text-white mb-1">Tạo Album Mới</h2>
                            <p class="text-white-50 mb-0 small">Lưu giữ khoảnh khắc âm nhạc của riêng bạn</p>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5 bg-dark-subtle">
                        <?= $message ?>

                        <form method="POST" enctype="multipart/form-data" autocomplete="off">
                            
                            <div class="form-floating mb-4">
                                <input type="text" name="name" id="albumName" 
                                       class="form-control rounded-pill bg-dark border-secondary text-white shadow-inner ps-4"
                                       placeholder="Tên album" required maxlength="100"
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                <label for="albumName" class="text-muted ps-4">Tên Album</label>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-white-50 small ps-3 mb-2">Ảnh bìa (Tùy chọn)</label>
                                
                                <div class="upload-zone rounded-4 position-relative overflow-hidden d-flex justify-content-center align-items-center" 
                                     id="dropZone" onclick="document.getElementById('coverInput').click()">
                                    
                                    <div class="text-center p-4 upload-placeholder" id="uploadPlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3 icon-bounce"></i>
                                        <h6 class="text-white fw-bold mb-1">Chọn ảnh hoặc kéo thả vào đây</h6>
                                        <p class="text-muted small mb-0">JPG, PNG, GIF (Max 5MB)</p>
                                    </div>

                                    <img id="previewImg" class="position-absolute w-100 h-100 object-fit-cover" style="display: none;">
                                    
                                    <input type="file" name="cover_image" id="coverInput" hidden accept="image/*">
                                </div>
                            </div>

                            <div class="d-grid gap-3 mt-5">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-lg fw-bold py-3 btn-animate">
                                    <i class="fas fa-plus-circle me-2"></i> Hoàn Tất Tạo Album
                                </button>
                                <a href="my_albums.php" class="btn btn-outline-light rounded-pill py-2 border-0 text-white-50">
                                    Hủy bỏ
                                </a>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/player.php'; include '../includes/footer.php'; ?>

<style>
/* THEME STYLES */
.bg-gradient-primary { background: linear-gradient(135deg, #00D4FF 0%, #7B2CBF 100%); }
.bg-dark-subtle { background-color: #1e1e2f; }
.shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7); }
.shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); }

/* ICON CIRCLE */
.icon-circle {
    width: 80px; height: 80px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(255,255,255,0.2);
}
.fa-spin-slow { animation: spin 10s linear infinite; }

/* UPLOAD ZONE */
.upload-zone {
    border: 2px dashed #444;
    background: rgba(0,0,0,0.2);
    min-height: 200px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.upload-zone:hover {
    border-color: #00D4FF;
    background: rgba(0, 212, 255, 0.05);
}
.icon-bounce { transition: transform 0.3s; }
.upload-zone:hover .icon-bounce { transform: translateY(-5px); }

/* INPUT STYLES */
.form-control:focus {
    background-color: #121212;
    border-color: #00D4FF;
    color: white;
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.15);
}
.form-floating label { color: #aaa; }

/* ANIMATION */
@keyframes spin { 100% { transform: rotate(360deg); } }
.btn-animate { transition: transform 0.2s; }
.btn-animate:active { transform: scale(0.98); }
</style>

<script>
// XỬ LÝ PREVIEW ẢNH
const coverInput = document.getElementById('coverInput');
const previewImg = document.getElementById('previewImg');
const placeholder = document.getElementById('uploadPlaceholder');
const dropZone = document.getElementById('dropZone');

coverInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            placeholder.style.display = 'none'; // Ẩn nội dung chữ
            dropZone.style.borderStyle = 'solid'; // Đổi viền thành nét liền
            dropZone.style.borderColor = '#00D4FF';
        }
        reader.readAsDataURL(file);
    }
});

// Xử lý hiệu ứng Drag & Drop (tùy chọn nâng cao)
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#00D4FF';
    dropZone.style.background = 'rgba(0, 212, 255, 0.1)';
});

dropZone.addEventListener('dragleave', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#444';
    dropZone.style.background = 'rgba(0,0,0,0.2)';
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#444';
    dropZone.style.background = 'rgba(0,0,0,0.2)';
    
    if (e.dataTransfer.files.length) {
        coverInput.files = e.dataTransfer.files;
        // Trigger event change thủ công để chạy hàm preview
        const event = new Event('change');
        coverInput.dispatchEvent(event);
    }
});
</script>