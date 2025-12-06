<?php

if (!defined('DB_INCLUDED_FROM_API')) {
    // If config not already required by the caller, require it relative to project root
    if (file_exists(__DIR__ . '/../config/database.php')) {
        require_once __DIR__ . '/../config/database.php';
    }
}

class AuthController {
    private $user;

    public function __construct() {
        require_once '../models/User.php';
        $this->user = new User();
    }

    // HÀM LOGIN MỚI – HOẠT ĐỘNG CHO CẢ WEB + API
    public function login($username, $password) {
        $userData = $this->user->findByUsername($username);

        if (!$userData) {
            return [
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
            ];
        }

        $stored = $userData['password'] ?? '';

        // Nếu là hash và đúng
        if (!empty($stored) && password_verify($password, $stored)) {
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                if (method_exists($this->user, 'updatePassword')) {
                    $this->user->updatePassword($userData['id'], $newHash);
                }
            }
            return [
                'success' => true,
                'user' => [
                    'id'       => $userData['id'],
                    'username' => $userData['username'],
                    'email'    => $userData['email'] ?? '',
                    'role'     => $userData['role'] ?? 'user'
                ]
            ];
        }

        // Nếu là mật khẩu legacy (plaintext)
        if ($stored === $password) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            if (method_exists($this->user, 'updatePassword')) {
                $this->user->updatePassword($userData['id'], $newHash);
            }
            return [
                'success' => true,
                'user' => [
                    'id'       => $userData['id'],
                    'username' => $userData['username'],
                    'email'    => $userData['email'] ?? '',
                    'role'     => $userData['role'] ?? 'user'
                ]
            ];
        }

        // Sai tài khoản hoặc mật khẩu
        return [
            'success' => false,
            'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
        ];
    }

    // HÀM REGISTER MỚI – TRẢ VỀ JSON CHO API
    public function register($username, $email, $password) {
        // Kiểm tra username đã tồn tại chưa
        if ($this->user->findByUsername($username)) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
        }

        // Mã hóa mật khẩu
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Thêm user mới
        $userId = $this->user->create($username, $email, $hashed);

        if ($userId) {
            return [
                'success' => true,
                'user' => [
                    'id'       => $userId,
                    'username' => $username,
                    'email'    => $email,
                    'role'     => 'user'
                ]
            ];
        } else {
            return ['success' => false, 'message' => 'Đăng ký thất bại, thử lại!'];
        }
    }
}

// If this file is accessed directly (e.g. form posts to controllers/AuthController.php?action=...),
// handle the action. When included by other files, this block will not execute.
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    session_start();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $ctrl = new AuthController();

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $res = $ctrl->login($username, $password);
        if ($res['success']) {
            $_SESSION['user_id'] = $res['user']['id'];
            $_SESSION['username'] = $res['user']['username'];
            $_SESSION['role'] = $res['user']['role'] ?? 'user';
            header('Location: ../views/home.php');
            exit;
        } else {
            // send back to login with a simple error via query param
            header('Location: ../login.php?error=' . urlencode($res['message'] ?? 'Đăng nhập thất bại'));
            exit;
        }
    }

    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        if ($password !== $confirm) {
            header('Location: ../register.php?error=' . urlencode('Mật khẩu không khớp'));
            exit;
        }
        $res = $ctrl->register($username, $email, $password);
        if ($res['success']) {
            $_SESSION['user_id'] = $res['user']['id'];
            $_SESSION['username'] = $res['user']['username'];
            header('Location: ../views/home.php');
            exit;
        } else {
            header('Location: ../register.php?error=' . urlencode($res['message'] ?? 'Đăng ký thất bại'));
            exit;
        }
    }
}