<?php
// FILE: models/User.php

class User {
    private $conn;

    public function __construct() {
        // Lấy biến kết nối $conn từ file config (global)
        if (!isset($GLOBALS['db_conn'])) {
            require_once __DIR__ . '/../config/database.php';
        }
        global $conn;
        $this->conn = $conn;
    }

    // --- PHẦN 1: CÁC HÀM CƠ BẢN (LOGIN/REGISTER) ---
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return null;
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    // Tìm user theo ID
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return null;
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    // Kiểm tra xem username hoặc email đã tồn tại chưa (trừ ID hiện tại ra)
    public function exists($username, $email, $excludeId = 0) {
        $sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return false;

        $stmt->bind_param("ssi", $username, $email, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0; // Trả về true nếu đã có người dùng khác trùng thông tin
    }

    public function create($username, $email, $password) {
        // Kiểm tra trùng lặp trước khi tạo
        if ($this->exists($username, $email)) {
            return false;
        }

        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return false;

        $role = 'user'; 
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function updatePassword($userId, $hashedPassword) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return false;

        $stmt->bind_param('si', $hashedPassword, $userId);
        return $stmt->execute();
    }

    // --- PHẦN 2: CÁC HÀM QUẢN TRỊ (ADMIN/STAFF) ---

    // Lấy danh sách tất cả user
    public function getAllUsers() {
        $sql = "SELECT * FROM users ORDER BY role ASC, created_at DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Cập nhật quyền (Role)
     */
    public function updateRole($id, $role) {
        $allowedRoles = ['admin', 'staff', 'user'];
        if (!in_array($role, $allowedRoles)) {
            return false; 
        }

        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return false;

        $stmt->bind_param("si", $role, $id);
        return $stmt->execute();
    }

    // Cập nhật thông tin cơ bản (Có kiểm tra trùng lặp)
    public function updateInfo($id, $username, $email) {
        // 1. Kiểm tra xem username/email mới có bị trùng với người khác không
        if ($this->exists($username, $email, $id)) {
            return false; // Trả về false nếu trùng
        }

        // 2. Nếu không trùng thì Update
        $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return false;

        $stmt->bind_param("ssi", $username, $email, $id);
        return $stmt->execute();
    }

    // Xóa người dùng
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) return false;

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>