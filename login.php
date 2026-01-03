<?php 
// 1. Xử lý Session & Redirect ngay đầu trang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) { 
    header('Location: views/home.php'); 
    exit; 
}

// 2. Xử lý thông báo
$alertMsg = '';
$alertType = '';

if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $alertMsg = "Bạn đã đăng xuất thành công!";
    $alertType = "success";
} elseif (isset($_GET['success'])) {
    $alertMsg = htmlspecialchars($_GET['success']);
    $alertType = "success";
} elseif (isset($_GET['error'])) {
    $alertMsg = htmlspecialchars($_GET['error']);
    $alertType = "danger"; // Màu đỏ
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập • ĐR</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;900&display=swap" rel="stylesheet">
  
  <style>
    :root {
        --primary: #00D4FF;
        --secondary: #9D4EDD;
        --bg: #0f0c29;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
        background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
        min-height: 100vh;
        font-family: 'Inter', sans-serif;
        color: white;
        overflow: hidden;
        position: relative;
    }

    /* --- BACKGROUND ANIMATION --- */
    .area {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle at 50% 50%, rgba(0,212,255,0.1), transparent 70%);
        pointer-events: none; z-index: 0;
    }
    .circles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; }
    .circles li {
        position: absolute; display: block; list-style: none;
        width: 20px; height: 20px; background: rgba(0,212,255,0.2);
        animation: animate 25s linear infinite;
        bottom: -150px; border-radius: 50%;
    }
    .circles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
    .circles li:nth-child(2) { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
    .circles li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
    .circles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
    .circles li:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
    
    @keyframes animate {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
        100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
    }

    /* --- LOGIN BOX --- */
    .login-box {
        background: rgba(20,20,40,0.85);
        padding: 50px 40px;
        border-radius: 28px;
        box-shadow: 0 30px 80px rgba(0,212,255,0.4);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        max-width: 450px; width: 100%;
        z-index: 10; position: relative;
        transition: transform 0.3s ease;
    }
    .login-box:hover { transform: translateY(-5px); }

    /* --- LOGO IMAGE STYLE --- */
    .logo-login-img {
        width: 100px; 
        height: auto;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        animation: floatLogo 3s ease-in-out infinite;
        margin-bottom: 10px;
    }

    @keyframes floatLogo {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    .text-gradient {
        font-size: 1.5rem; font-weight: 700; text-align: center;
        background: linear-gradient(135deg, #fff, #aaa);
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent; color: transparent;
        display: inline-block; width: 100%; margin-bottom: 30px;
    }

    /* --- FORM CONTROLS --- */
    .input-group-custom { position: relative; margin-bottom: 20px; }
    .input-group-custom i.icon-start {
        position: absolute; top: 50%; left: 20px; transform: translateY(-50%);
        color: #aaa; z-index: 2; transition: 0.3s;
    }
    .form-control {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 50px;
        padding: 16px 20px 16px 50px; /* Chừa chỗ cho icon trái */
        color: white; font-size: 1rem;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        background: rgba(255,255,255,0.1);
        border-color: var(--primary);
        box-shadow: 0 0 20px rgba(0,212,255,0.3);
        color: white; outline: none;
    }
    .form-control:focus + i.icon-start { color: var(--primary); }
    .form-control::placeholder { color: rgba(255,255,255,0.4); }

    /* Toggle Password */
    .toggle-password {
        position: absolute; top: 50%; right: 20px; transform: translateY(-50%);
        color: #aaa; cursor: pointer; z-index: 3; transition: 0.3s;
    }
    .toggle-password:hover { color: white; }

    /* --- BUTTONS --- */
    .btn-login {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; padding: 16px; border: none; border-radius: 50px;
        font-size: 1.2rem; font-weight: 800; width: 100%;
        transition: all 0.4s ease;
        box-shadow: 0 10px 30px rgba(0,212,255,0.3);
        text-transform: uppercase; letter-spacing: 1px;
    }
    .btn-login:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 50px rgba(0,212,255,0.6);
        background: linear-gradient(135deg, var(--secondary), var(--primary));
    }

    a { color: var(--primary); text-decoration: none; font-weight: 600; transition: 0.3s; }
    a:hover { color: white; text-shadow: 0 0 10px var(--primary); }

    /* Alerts */
    .custom-alert {
        padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem;
        display: flex; align-items: center; gap: 10px;
    }
    .custom-alert.danger { background: rgba(231, 76, 60, 0.2); border: 1px solid #e74c3c; color: #ff6b6b; }
    .custom-alert.success { background: rgba(46, 204, 113, 0.2); border: 1px solid #2ecc71; color: #2ecc71; }
  </style>
</head>
<body>

<div class="area">
  <ul class="circles">
    <li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li>
  </ul>
</div>

<div class="d-flex align-items-center justify-content-center min-vh-100">
  <div class="login-box animate-fade-in">
    
    <div class="text-center mb-4">
        <img src="assets/songs/images/logo.jpg" alt="ĐR" class="logo-login-img">
    </div>
    
    <div class="text-gradient">Chào mừng trở lại</div>

    <?php if ($alertMsg): ?>
        <div class="custom-alert <?= $alertType ?>">
            <i class="fas <?= $alertType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= $alertMsg ?>
        </div>
    <?php endif; ?>

    <form action="controllers/AuthController.php?action=login" method="POST">
        
        <div class="input-group-custom">
            <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required autocomplete="username">
            <i class="fas fa-user icon-start"></i>
        </div>
        
        <div class="input-group-custom">
            <input type="password" name="password" id="pass" class="form-control" placeholder="Mật khẩu" required autocomplete="current-password">
            <i class="fas fa-lock icon-start"></i>
            <i class="fas fa-eye toggle-password" onclick="togglePass(this)"></i>
        </div>

        <button type="submit" class="btn-login mt-4">
            ĐĂNG NHẬP <i class="fas fa-arrow-right ms-2"></i>
        </button>
    </form>

    <p class="mt-4 text-center text-white-50">
        Chưa có tài khoản? 
        <a href="register.php">Đăng ký ngay</a>
    </p>
  </div>
</div>

<script>
function togglePass(icon) {
  const pass = document.getElementById("pass");
  if (pass.type === "password") {
    pass.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    pass.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}
</script>
</body>
</html>