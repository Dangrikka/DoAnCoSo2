<?php
// index.php - TRANG LOADING
// 1. Khởi tạo session ngay đầu file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Lưu ý: Trang loading thường không cần include header.php vì nó có giao diện riêng
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ĐR • Đang tải...</title>
  
  <link rel="icon" type="image/png" href="assets/images/logo.png">
  
  <style>
    /* Reset cơ bản */
    body { margin: 0; padding: 0; background: #000; font-family: sans-serif; overflow: hidden; }

    .loader {
      position: fixed;
      inset: 0;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); /* Nền đồng bộ với web */
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 9999;
    }

    /* CSS CHO LOGO ẢNH */
    .logo-img {
        width: 180px; /* Kích thước logo */
        height: auto;
        object-fit: contain;
        margin-bottom: 30px;
        /* Hiệu ứng logo thở sáng */
        animation: pulse 2s infinite ease-in-out;
        filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.4));
    }

    .spinner {
      width: 60px;
      height: 60px;
      border: 5px solid rgba(255,255,255,0.1);
      border-top: 5px solid #00D4FF; /* Màu xanh chủ đạo */
      border-right: 5px solid #9D4EDD; /* Màu tím chủ đạo */
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 20px;
    }

    .loading-text {
        color: #aaa;
        font-size: 0.9rem;
        letter-spacing: 1px;
        animation: fadeText 2s infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }
    
    @keyframes pulse {
        0% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.4)); }
        50% { transform: scale(1.05); filter: drop-shadow(0 0 25px rgba(0, 212, 255, 0.8)); }
        100% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.4)); }
    }

    @keyframes fadeText {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }
  </style>
</head>
<body>

<div class="loader">
  <img src="assets/songs/images/logo.jpg" alt="Logo" class="logo-img">
  
  <div class="spinner"></div>
  <p class="loading-text">Đang tải trải nghiệm âm nhạc tuyệt vời...</p>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
  <script>
    setTimeout(() => {
      // Đã đăng nhập -> Vào trang chủ
      window.location.href = 'views/home.php';
    }, 2000); // Tăng lên 2s để người dùng kịp nhìn thấy logo đẹp
  </script>
<?php else: ?>
  <script>
    setTimeout(() => {
      // Chưa đăng nhập -> Về trang login
      window.location.href = 'login.php';
    }, 2000);
  </script>
<?php endif; ?>

</body>
</html>