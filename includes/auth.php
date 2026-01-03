<?php
// includes/auth.php

// Hàm kiểm tra quyền truy cập
function check_role($allowed_roles) {
    // Nếu chưa đăng nhập -> Đá về login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../login.php');
        exit;
    }

    // Nếu role hiện tại không nằm trong danh sách cho phép -> Báo lỗi hoặc đá về trang chủ
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        echo "<script>alert('Bạn không có quyền truy cập trang này!'); window.location.href='../views/home.php';</script>";
        exit;
    }
}
?>