<?php
// FILE: controllers/UserController.php

// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // 1. Lấy danh sách User (Read)
    public function index() {
        return $this->userModel->getAllUsers();
    }

    // 2. Lấy thông tin 1 User
    public function getUser($id) {
        $id = (int)$id;
        if ($id <= 0) return null;
        return $this->userModel->findById($id);
    }

    // 3. Cập nhật thông tin cơ bản
    public function updateInfo($id, $username, $email) {
        // Validate cơ bản
        if (empty($username) || empty($email)) {
            return ['success' => false, 'message' => 'Vui lòng nhập đủ thông tin!'];
        }
        
        // Gọi Model (Model đã xử lý check trùng lặp)
        if ($this->userModel->updateInfo((int)$id, $username, $email)) {
            return ['success' => true, 'message' => 'Cập nhật thông tin thành công!'];
        }
        
        // Nếu false, khả năng cao là do trùng email/username
        return ['success' => false, 'message' => 'Lỗi: Tên đăng nhập hoặc Email đã tồn tại.'];
    }

    // 4. Xử lý đổi quyền (Phân quyền)
    public function changeRole($id, $newRole) {
        $id = (int)$id;
        $currentUserId = $_SESSION['user_id'] ?? 0;

        // BẢO VỆ: Không cho phép tự hạ quyền của chính mình
        if ($id === $currentUserId) {
            return false; 
        }

        // Chỉ cho phép các role hợp lệ
        $allowedRoles = ['admin', 'staff', 'user'];
        if (!in_array($newRole, $allowedRoles)) {
            return false;
        }
        
        return $this->userModel->updateRole($id, $newRole);
    }

    // 5. Xử lý xóa user
    public function delete($id) {
        $id = (int)$id;
        $currentUserId = $_SESSION['user_id'] ?? 0;

        // BẢO VỆ: Không cho phép tự xóa chính mình
        if ($id === $currentUserId) {
            return false;
        }

        return $this->userModel->delete($id);
    }
}
?>