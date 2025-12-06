<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hàm kiểm tra quyền – siêu ngắn gọn, an toàn
function check_role(array $allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: login.php");
        exit;
    }
}
?>