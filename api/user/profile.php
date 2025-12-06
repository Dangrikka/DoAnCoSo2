<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Chưa đăng nhập"]);
    exit;
}

echo json_encode([
    "success" => true,
    "user" => [
        "id" => $_SESSION['user_id'] ?? null,
        "username" => $_SESSION['username'] ?? '',
        "role" => $_SESSION['role'] ?? 'user'
    ]
]);