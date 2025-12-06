<?php
// create_album.php – TẠO ALBUM MỚI – ĐẸP LUNG LINH 2025

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../controllers/AlbumController.php';

$albumCtrl = new AlbumController();
$message = '';

// XỬ LÝ FORM KHI NHẤN TẠO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $message = '<div class="alert alert-danger">Vui lòng nhập tên album!</div>';
    } elseif (strlen($name) > 100) {
        $message = '<div class="alert alert-danger">Tên album không được quá 100 ký tự!</div>';
    } else {
        $albumId = $albumCtrl->create($_SESSION['user_id'], $name);
        if ($albumId) {
            header('Location: my_albums.php?created=1');
            exit;
        } else {
            $message = '<div class="alert alert-danger">Có lỗi xảy ra. Vui lòng thử lại!</div>';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">

                <!-- CARD TẠO ALBUM SIÊU ĐẸP -->
                <div class="card bg-dark border-0 shadow-2xl rounded-4 overflow-hidden">
                    <div class="card-header bg-gradient-primary text-white text-center py-5 position-relative">
                        <i class="fas fa-compact-disc fa-5x mb-3 fa-spin-slow"></i>
                        <h1 class="display-5 fw-bold mb-0">Tạo Album Mới</h1>
                        <p class="mb-0 opacity-90">Lưu giữ những bài hát yêu thích của bạn</p>
                    </div>

                    <div class="card-body p-5">
                        <?= $message ?>

                        <form method="POST" autocomplete="off">
                            <div class="mb-4">
                                <label class="form-label text-white fs-5 mb-3">
                                    <i class="fas fa-edit me-2 text-primary"></i> Tên album
                                </label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control form-control-lg rounded-pill text-center bg-secondary border-0 text-white shadow-inner"
                                       placeholder="Nhập tên album của bạn..." 
                                       maxlength="100"
                                       required 
                                       autofocus
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                                <div class="form-text text-muted text-center mt-2">
                                    Tối đa 100 ký tự • Có thể thay đổi sau
                                </div>
                            </div>

                            <div class="d-grid gap-3 mt-5">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-lg py-3 fs-5 fw-bold">
                                    <i class="fas fa-plus me-2"></i> Tạo Album Ngay
                                </button>
                                <a href="my_albums.php" class="btn btn-outline-light btn-lg rounded-pill py-3">
                                    <i class="fas fa-arrow-left me-2"></i> Quay lại
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- FOOTER CARD -->
                    <div class="card-footer bg-gradient-dark text-center py-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Sau khi tạo, bạn có thể thêm bài hát vào album bất kỳ lúc nào!
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/player.php'; include '../includes/footer.php'; ?>
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #00D4FF, #8A2BE2) !important;
}

.bg-gradient-dark {
    background: linear-gradient(135deg, #1a1a2e, #16213e) !important;
}

.shadow-2xl {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 50px rgba(0, 212, 255, 0.3) !important;
}

.shadow-inner {
    box-shadow: inset 0 5px 15px rgba(0,0,0,0.4) !important;
}

.form-control:focus {
    border-color: #00D4FF !important;
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.3) !important;
    background: rgba(0, 212, 255, 0.1) !important;
}

.btn-primary {
    background: linear-gradient(135deg, #00D4FF, #00A1D6);
    border: none;
    transition: all 0.4s ease;
}

.btn-primary:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 212, 255, 0.5) !important;
    background: linear-gradient(135deg, #00ffcc, #00D4FF);
}

.fa-spin-slow {
    animation: spin 8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.card {
    border: 2px solid transparent;
    transition: all 0.4s ease;
}

.card:hover {
    border-color: #00D4FF;
    box-shadow: 0 30px 70px rgba(0, 212, 255, 0.4) !important;
}
</style>