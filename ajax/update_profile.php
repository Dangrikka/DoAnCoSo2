<?php
// ajax/update_profile.php
session_start();
require_once '../config/database.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $uploadDir = '../assets/songs/images/'; // Thư mục lưu ảnh (dùng chung với ảnh bài hát cho tiện)

    // Validate cơ bản
    if (empty($username) || empty($email)) {
        echo "<script>alert('Vui lòng điền đầy đủ tên và email!'); window.history.back();</script>";
        exit;
    }

    // 3. Xử lý Upload Avatar (nếu có chọn file)
    $avatarSql = ""; 
    $params = [$username, $email];
    $types = "ss"; // string, string

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $file = $_FILES['avatar'];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        
        // Lấy đuôi file
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Kiểm tra định dạng
        if (!in_array($fileExt, $allowed)) {
            echo "<script>alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!'); window.history.back();</script>";
            exit;
        }

        // Kiểm tra dung lượng (Max 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            echo "<script>alert('File ảnh quá lớn! Vui lòng chọn ảnh dưới 5MB.'); window.history.back();</script>";
            exit;
        }

        // Tạo tên file mới để tránh trùng lặp
        $newFileName = 'avatar_' . $user_id . '_' . time() . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;

        // Di chuyển file vào thư mục
        if (move_uploaded_file($fileTmp, $destination)) {
            // Thêm vào câu lệnh SQL
            $avatarSql = ", avatar = ?";
            $params[] = $newFileName;
            $types .= "s";
        } else {
            echo "<script>alert('Lỗi khi tải ảnh lên server!'); window.history.back();</script>";
            exit;
        }
    }

    // 4. Cập nhật Database
    // SQL động: Nếu có avatar thì update thêm cột avatar, nếu không thì giữ nguyên
    $sql = "UPDATE users SET username = ?, email = ? $avatarSql WHERE id = ?";
    
    // Thêm user_id vào tham số cuối cùng
    $params[] = $user_id;
    $types .= "i";

    if ($stmt = $GLOBALS['db_conn']->prepare($sql)) {
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            // Cập nhật lại Session để hiển thị tên mới ngay lập tức
            $_SESSION['username'] = $username;
            
            // Thành công -> Quay lại trang profile
            header('Location: ../views/profile.php?status=success');
            exit;
        } else {
            echo "<script>alert('Lỗi CSDL: " . $stmt->error . "'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Lỗi hệ thống!'); window.history.back();</script>";
    }

} else {
    // Nếu truy cập trực tiếp file này mà không post
    header('Location: ../views/profile.php');
}
?>