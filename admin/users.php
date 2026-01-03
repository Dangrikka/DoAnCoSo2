<?php
// admin/users.php – QUẢN LÝ USER & PHÂN QUYỀN (FINAL VERSION)

// 1. Auth & Config
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
require_once '../includes/auth.php'; // Đảm bảo file này có hàm check_role
require_once '../controllers/UserController.php';

// Chỉ Admin mới được truy cập
check_role(['admin']);

$userCtrl = new UserController();
$msg = '';
$msgType = '';

// 2. XỬ LÝ FORM (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // ACTION: Đổi quyền (Role)
    if ($_POST['action'] === 'update_role') {
        $uid = (int)$_POST['user_id'];
        $newRole = $_POST['role'];
        
        // Bảo vệ: Không cho tự hạ quyền Admin của chính mình
        if ($uid == $_SESSION['user_id'] && $newRole !== 'admin') {
             $msg = "Bạn không thể tự hạ quyền Admin của chính mình!";
             $msgType = "error";
        } else {
            if ($userCtrl->changeRole($uid, $newRole)) {
                $msg = "Đã cập nhật quyền thành công!";
                $msgType = "success";
            } else {
                $msg = "Lỗi cập nhật hoặc quyền không hợp lệ!";
                $msgType = "error";
            }
        }
    }
    
    // ACTION: Xóa user
    if ($_POST['action'] === 'delete') {
        $uid = (int)$_POST['user_id'];
        // Bảo vệ: Không cho tự xóa chính mình
        if ($uid == $_SESSION['user_id']) {
            $msg = "Bạn không thể tự xóa tài khoản của mình!";
            $msgType = "error";
        } else {
            if ($userCtrl->delete($uid)) {
                $msg = "Đã xóa người dùng khỏi hệ thống!";
                $msgType = "success";
            } else {
                $msg = "Lỗi khi xóa người dùng!";
                $msgType = "error";
            }
        }
    }
}

// 3. LẤY DANH SÁCH USER
$users = $userCtrl->index();

include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Quản lý người dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- CSS GIAO DIỆN --- */
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            font-family: 'Inter', sans-serif; color: #fff; padding: 30px; min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .text-gradient {
            background: linear-gradient(135deg, #00D4FF, #9D4EDD);
            background-clip: text; -webkit-text-fill-color: transparent;
            font-size: 2rem; font-weight: 800; margin: 0;
        }
        .btn-back {
            text-decoration: none; color: #a0a0b0; font-weight: 600;
            padding: 10px 20px; border-radius: 50px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s; display: flex; align-items: center; gap: 8px;
        }
        .btn-back:hover { background: rgba(255,255,255,0.1); color: #fff; border-color: #00D4FF; }
        
        .table-container {
            background: rgba(20, 20, 40, 0.85); backdrop-filter: blur(16px);
            border-radius: 20px; padding: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { text-align: left; padding: 15px; color: #a0a0b0; text-transform: uppercase; font-size: 0.85rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        td { padding: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .user-info { display: flex; align-items: center; gap: 15px; }
        .avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: linear-gradient(135deg, #00D4FF, #005bea);
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2rem; color: white;
            box-shadow: 0 4px 10px rgba(0, 212, 255, 0.3);
        }
        .user-details h4 { margin: 0; font-size: 1rem; color: #fff; }
        .user-details span { font-size: 0.85rem; color: #aaa; }

        .badge { padding: 6px 12px; border-radius: 30px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
        .role-admin { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .role-staff { background: rgba(241, 196, 15, 0.2); color: #f1c40f; border: 1px solid #f1c40f; }
        .role-user  { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }

        .action-form { display: flex; gap: 10px; align-items: center; }
        select.role-select {
            background: rgba(0,0,0,0.3); color: #fff; border: 1px solid rgba(255,255,255,0.2);
            padding: 6px 10px; border-radius: 8px; cursor: pointer; outline: none;
        }
        select.role-select:focus { border-color: #00D4FF; }
        .btn-action { border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 0.9rem; transition: 0.2s; }
        .btn-save { background: rgba(0, 212, 255, 0.2); color: #00D4FF; }
        .btn-save:hover { background: #00D4FF; color: #000; }
        .btn-del { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .btn-del:hover { background: #e74c3c; color: #fff; }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .success { background: rgba(46,204,113,0.2); color: #2ecc71; border: 1px solid #2ecc71; }
        .error { background: rgba(231,76,60,0.2); color: #e74c3c; border: 1px solid #e74c3c; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-nav">
        <h2 class="text-gradient"><i class="fa-solid fa-users-gear"></i> QUẢN LÝ USER</h2>
        <a href="../views/home.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Quay lại Trang Chủ
        </a>
    </div>

    <?php if ($msg): ?>
        <div class="alert <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
        <script>setTimeout(() => { document.querySelector('.alert').style.display='none'; }, 3000);</script>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Thông tin User</th>
                    <th>Ngày tham gia</th>
                    <th>Vai trò hiện tại</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <?php
                        // --- XỬ LÝ FIX LỖI ROLE BỊ TRỐNG ---
                        // Nếu role bị null hoặc rỗng -> Gán mặc định là 'user' để hiển thị
                        $currentRole = !empty($u['role']) ? $u['role'] : 'user';

                        // Chọn màu sắc Badge
                        $badgeClass = 'role-user'; // Mặc định xanh lá
                        if ($currentRole === 'admin') $badgeClass = 'role-admin';
                        if ($currentRole === 'staff') $badgeClass = 'role-staff';
                    ?>

                    <tr>
                        <td style="color:#666">#<?= $u['id'] ?></td>
                        <td>
                            <div class="user-info">
                                <div class="avatar">
                                    <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                </div>
                                <div class="user-details">
                                    <h4><?= htmlspecialchars($u['username']) ?></h4>
                                    <span><?= htmlspecialchars($u['email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td style="color:#ccc"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        
                        <td>
                            <span class="badge <?= $badgeClass ?>"><?= ucfirst($currentRole) ?></span>
                        </td>
                        
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                
                                <select name="role" class="role-select" onchange="this.style.borderColor='#00D4FF'">
                                    <option value="user" <?= $currentRole == 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="staff" <?= $currentRole == 'staff' ? 'selected' : '' ?>>Staff</option>
                                    <option value="admin" <?= $currentRole == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>

                                <button type="submit" name="action" value="update_role" class="btn-action btn-save" title="Lưu thay đổi">
                                    <i class="fa-solid fa-check"></i>
                                </button>

                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <button type="submit" name="action" value="delete" class="btn-action btn-del" 
                                            onclick="return confirm('CẢNH BÁO: Xóa user <?= htmlspecialchars($u['username']) ?>?');" title="Xóa User">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>