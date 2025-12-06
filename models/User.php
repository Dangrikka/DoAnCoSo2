<?php
// models/User.php

class User {
    private $conn;

    public function __construct() {
        // Sử dụng biến $conn toàn cục đã được nạp ở các file view
        global $conn;
        $this->conn = $conn;
    }

    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            // prepare failed (missing table/column or SQL error)
            throw new \RuntimeException('DB prepare failed in findByUsername: ' . $this->conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    public function create($username, $email, $password) {
        // Use parameter for role to avoid potential SQL quoting/reserved word issues
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new \RuntimeException('DB prepare failed in create: ' . $this->conn->error);
        }
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
        if ($stmt === false) {
            throw new \RuntimeException('DB prepare failed in updatePassword: ' . $this->conn->error);
        }
        $stmt->bind_param('si', $hashedPassword, $userId);
        return $stmt->execute();
    }
}