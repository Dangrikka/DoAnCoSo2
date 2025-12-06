<?php
$sessionStarted = session_status() === PHP_SESSION_ACTIVE;
if (!$sessionStarted) session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$data = json_decode(file_get_contents("php://input"), true) ?: $_POST;

$username = trim($data['username'] ?? '');
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$confirm  = $data['confirm'] ?? '';

if ($password !== $confirm) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Mật khẩu không khớp"]);
    exit;
}

$auth = new AuthController();
$result = $auth->register($username, $email, $password);

if ($result['success']) {
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['username'] = $result['user']['username'];
    echo json_encode(["success" => true, "user" => $result['user']]);
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $result['message']]);
}