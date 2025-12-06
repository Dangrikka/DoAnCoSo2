<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? $_POST['username'] ?? '';
$password = $data['password'] ?? $_POST['password'] ?? '';

$auth = new AuthController();
$result = $auth->login($username, $password);
header('Content-Type: application/json; charset=utf-8');
if ($result['success']) {
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['username'] = $result['user']['username'];
    $_SESSION['role'] = $result['user']['role'];
    echo json_encode(["success" => true, "user" => $result['user']]);
} else {
    http_response_code(401);
    $msg = $result['message'] ?? "Sai tài khoản hoặc mật khẩu";
    echo json_encode(["success" => false, "message" => $msg]);
}