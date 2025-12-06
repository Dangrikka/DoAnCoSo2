<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NHẠC HAY • Đang tải...</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .loader {
      position: fixed;
      inset: 0;
      background: #000;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      gap: 30px;
    }
    .logo-loading {
      font-family: 'Orbitron', sans-serif;
      font-size: 68px;
      background: linear-gradient(90deg, #00D4FF, #9D4EDD);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: glow 2s ease-in-out infinite;
    }
    .spinner {
      width: 90px;
      height: 90px;
      border: 8px solid rgba(0,212,255,0.3);
      border-top: 8px solid #00D4FF;
      border-radius: 50%;
      animation: spin 1.2s linear infinite;
    }
    @keyframes glow { 0%,100% { opacity: 0.8; } 50% { opacity: 1; } }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<div class="loader">
  <div class="logo-loading">MUSICSTREAM</div>
  <div class="spinner"></div>
  <p class="text-muted">Đang tải trải nghiệm âm nhạc tuyệt vời...</p>
</div>

  <?php session_start(); ?>
<?php if (isset($_SESSION['user_id'])): ?>
  <script>
    setTimeout(() => {
      window.location.href = 'views/home.php';
    }, 1800);
  </script>
<?php else: ?>
  <script>
    setTimeout(() => {
      window.location.href = 'login.php';
    }, 1800);
  </script>
<?php endif; ?>

</body>
</html>