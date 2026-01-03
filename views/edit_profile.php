<?php
session_start();
require_once '../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// 2. Xử lý Form Submit (Cập nhật thông tin)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Validate cơ bản
    if (empty($username) || empty($email)) {
        $error = "Vui lòng nhập tên và email!";
    } else {
        // --- XỬ LÝ ẢNH ĐẠI DIỆN ---
        $avatarSqlPart = "";
        $types = "ss"; // username, email
        $params = [$username, $email];

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $filename = $_FILES['avatar']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed) && $_FILES['avatar']['size'] <= 5000000) { // Max 5MB
                $newFileName = "avatar_" . $user_id . "_" . time() . "." . $ext;
                $dest = "../assets/songs/images/" . $newFileName;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                    $avatarSqlPart = ", avatar = ?";
                    $params[] = $newFileName;
                    $types .= "s";
                    
                    // Cập nhật session avatar luôn
                    $_SESSION['avatar'] = $newFileName; 
                }
            } else {
                $error = "Ảnh không hợp lệ hoặc quá lớn (>5MB).";
            }
        }

        // --- XỬ LÝ ĐỔI MẬT KHẨU ---
        $passSqlPart = "";
        if (!empty($_POST['new_password'])) {
            $old_pass = $_POST['old_password'];
            $new_pass = $_POST['new_password'];
            $confirm_pass = $_POST['confirm_password'];

            // Lấy pass cũ từ DB để so sánh
            $stmtCheck = $GLOBALS['db_conn']->prepare("SELECT password FROM users WHERE id = ?");
            $stmtCheck->bind_param("i", $user_id);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result()->fetch_assoc();

            if (password_verify($old_pass, $resCheck['password'])) {
                if ($new_pass === $confirm_pass) {
                    if (strlen($new_pass) >= 6) {
                        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                        $passSqlPart = ", password = ?";
                        $params[] = $hashed_pass;
                        $types .= "s";
                    } else {
                        $error = "Mật khẩu mới phải từ 6 ký tự trở lên.";
                    }
                } else {
                    $error = "Mật khẩu xác nhận không khớp.";
                }
            } else {
                $error = "Mật khẩu hiện tại không đúng.";
            }
        }

        // --- THỰC HIỆN UPDATE NẾU KHÔNG CÓ LỖI ---
        if (empty($error)) {
            $params[] = $user_id; // Thêm ID vào cuối
            $types .= "i";

            $sql = "UPDATE users SET username = ?, email = ? $avatarSqlPart $passSqlPart WHERE id = ?";
            $stmt = $GLOBALS['db_conn']->prepare($sql);
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                    // 1. Cập nhật Session Tên hiển thị
                    $_SESSION['username'] = $username; 

                    // 2. Cập nhật Session Avatar (Quan trọng: để Sidebar đổi ảnh ngay)
                    if (isset($newFileName)) {
                        $_SESSION['avatar'] = $newFileName;
                    }

                    // 3. Chuyển hướng về trang Profile
                    header('Location: profile.php');
                    exit; 
                } else {
                    $error = "Lỗi hệ thống: " . $stmt->error;
                }
        }
    }
}

// 3. Lấy thông tin mới nhất để hiển thị
$stmt = $GLOBALS['db_conn']->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4 px-lg-5">
        
        <div class="d-flex align-items-center justify-content-between mb-5 animate-fade-in">
            <div>
                <a href="profile.php" class="text-muted text-decoration-none mb-2 d-inline-block hover-white">
                    <i class="fas fa-arrow-left me-2"></i> Quay lại hồ sơ
                </a>
                <h1 class="display-4 fw-bold text-white mb-0">Chỉnh sửa hồ sơ</h1>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success bg-success bg-opacity-25 text-success border-success border-opacity-25 rounded-4 mb-4">
                <i class="fas fa-check-circle me-2"></i> <?= $msg ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger bg-danger bg-opacity-25 text-danger border-danger border-opacity-25 rounded-4 mb-4">
                <i class="fas fa-exclamation-circle me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <form method="POST" enctype="multipart/form-data" class="edit-profile-card p-4 p-md-5 rounded-4 shadow-lg">
                    
                    <div class="row g-5">
                        <div class="col-md-4 text-center">
                            <div class="avatar-upload-zone position-relative d-inline-block mb-3">
                                <img src="../assets/songs/images/<?= !empty($user['avatar']) ? $user['avatar'] : 'avatar.jpg' ?>" 
                                     id="avatarPreview"
                                     class="rounded-circle shadow-lg object-fit-cover" 
                                     style="width: 200px; height: 200px; border: 4px solid rgba(255,255,255,0.1);"
                                     onerror="this.src='../assets/songs/images/avatar.jpg'">
                                
                                <label for="fileInput" class="upload-btn">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="fileInput" name="avatar" class="d-none" accept="image/*" onchange="previewImage(this)">
                            </div>
                            <p class="text-muted small">
                                Cho phép: JPG, PNG, WEBP.<br>Tối đa 5MB.
                            </p>
                        </div>

                        <div class="col-md-8">
                            <h4 class="text-white fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-3">Thông tin cơ bản</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold small">Tên hiển thị</label>
                                    <input type="text" name="username" class="form-control form-control-lg bg-dark text-white border-secondary" 
                                           value="<?= htmlspecialchars($user['username']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold small">Email</label>
                                    <input type="email" name="email" class="form-control form-control-lg bg-dark text-white border-secondary" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                            </div>

                            <h4 class="text-white fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-3 mt-5">
                                Đổi mật khẩu <span class="text-muted fs-6 fw-normal ms-2">(Bỏ trống nếu không đổi)</span>
                            </h4>

                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold small">Mật khẩu hiện tại</label>
                                <div class="position-relative">
                                    <input type="password" name="old_password" class="form-control form-control-lg bg-dark text-white border-secondary">
                                    <i class="fas fa-lock position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold small">Mật khẩu mới</label>
                                    <input type="password" name="new_password" class="form-control form-control-lg bg-dark text-white border-secondary">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted fw-bold small">Xác nhận mật khẩu mới</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-lg bg-dark text-white border-secondary">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-5">
                                <a href="profile.php" class="btn btn-outline-secondary px-4 rounded-pill fw-bold">Hủy bỏ</a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold shadow-lg hover-scale">
                                    Lưu thay đổi
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/player.php'; ?>
<?php include '../includes/footer.php'; ?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<style>
/* CSS RIÊNG CHO TRANG EDIT PROFILE */
.edit-profile-card {
    background: rgba(20, 20, 40, 0.8);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

/* Avatar Upload Button */
.upload-btn {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 45px; height: 45px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.upload-btn:hover {
    transform: scale(1.15) rotate(15deg);
    background: white; color: var(--primary);
}

/* Form Styles */
.form-control {
    border: 1px solid rgba(255,255,255,0.1) !important;
    transition: all 0.3s;
}
.form-control:focus {
    background: rgba(255,255,255,0.05) !important;
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 4px rgba(0, 212, 255, 0.1);
    color: white !important;
}

.hover-white:hover { color: white !important; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .edit-profile-card { padding: 1.5rem !important; }
}
</style>

<script>
// JS Xem trước ảnh ngay lập tức
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>