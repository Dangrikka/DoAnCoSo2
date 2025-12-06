<?php
// register.php – ĐÃ SỬA HOÀN HẢO, ĐẸP NHƯ SPOTIFY 2025

// Nếu đã đăng nhập → tự động về trang chủ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: views/home.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký • MusicVN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

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

        /* Hiệu ứng nền động */
        .area {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(0,212,255,0.1), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        .circles {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden;
        }
        .circles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px; height: 20px;
            background: rgba(0,212,255,0.2);
            animation: animate 25s linear infinite;
            bottom: -150px;
            border-radius: 50%;
        }
        .circles li:nth-child(1)  { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .circles li:nth-child(2)  { left: 10%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 12s; }
        .circles li:nth-child(3)  { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
        .circles li:nth-child(4)  { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        .circles li:nth-child(5)  { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
        .circles li:nth-child(6)  { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
        .circles li:nth-child(7)  { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
        .circles li:nth-child(8)  { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
        .circles li:nth-child(9)  { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
        .circles li:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }

        @keyframes animate {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

        .login-box {
            background: rgba(20,20,40,0.95);
            padding: 50px 40px;
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0,212,255,0.4);
            border: 1px solid rgba(0,212,255,0.3);
            backdrop-filter: blur(16px);
            max-width: 480px;
            width: 100%;
            z-index: 10;
            position: relative;
        }
        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 4rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        .text-gradient {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #00D4FF, #9D4EDD);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-control {
            background: rgba(40,40,70,0.9);
            border: 1px solid #555;
            border-radius: 16px;
            padding: 16px 20px;
            color: white;
            font-size: 1.1rem;
            margin-bottom: 20px;
            transition: all 0.4s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 25px rgba(0,212,255,0.5);
            outline: none;
        }
        .btn-login {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 18px;
            border: none;
            border-radius: 50px;
            font-size: 1.4rem;
            font-weight: 800;
            width: 100%;
            transition: all 0.5s;
            box-shadow: 0 15px 40px rgba(0,212,255,0.5);
        }
        .btn-login:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 60px rgba(0,212,255,0.7);
        }
        a { color: var(--primary); text-decoration: none; font-weight: 600; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="area">
    <ul class="circles">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
</div>

<div class="d-flex align-items-center justify-content-center min-vh-100">
    <div class="login-box">
        <h3 class="text-gradient mb-4">Tạo tài khoản miễn phí</h3>

        <form action="controllers/AuthController.php?action=register" method="POST" class="mt-4">
            <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required>
            <input type="email" name="email" class="form-control" placeholder="Email của bạn" required>
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu (tối thiểu 6 ký tự)" required minlength="6">
            <input type="password" name="confirm" class="form-control" placeholder="Nhập lại mật khẩu" required minlength="6">

            <button type="submit" class="btn-login mt-4">
                <i class="fas fa-user-plus"></i> ĐĂNG KÝ NGAY
            </button>
        </form>

        <p class="mt-4 text-muted">
            Đã có tài khoản? 
            <a href="login.php">Đăng nhập ngay</a>
        </p>
    </div>
</div>

</body>
</html>