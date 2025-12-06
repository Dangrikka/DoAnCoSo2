<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<div class="logout-success">
    <i class="fas fa-check-circle"></i>
    Bạn đã đăng xuất thành công! Hẹn gặp lại nhé 
</div>
<?php endif; ?>

<?php 
session_start(); 
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
  <title>Đăng nhập • NHẠC HAY</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="area">
  <ul class="circles">
    <li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li>
  </ul>
</div>

<div class="d-flex align-items-center justify-content-center min-vh-100">
  <div class="card shadow-lg" style="width: 420px;">
    <div class="card-body p-5 text-center">
      
      <div class="logo mb-4">NHẠC HAY</div>
      
      <h3 class="text-gradient mb-4">Đăng nhập</h3>

      <form action="controllers/AuthController.php?action=login" method="POST">
        <input type="text" name="username" class="form-control mb-3" placeholder="Tên đăng nhập" required>
        
        <div class="position-relative">
          <input type="password" name="password" id="pass" class="form-control mb-4" placeholder="Mật khẩu" required>
          <i class="fas fa-eye position-absolute end-0 top-50 translate-middle-y me-4 text-white" 
             onclick="togglePass()" style="cursor:pointer; z-index:10;" id="eye"></i>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold">
          ĐĂNG NHẬP
        </button>
      </form>

      <p class="mt-4 text-muted">
        Chưa có tài khoản? 
        <a href="register.php" style="color:#00D4FF; text-decoration:none; font-weight:500;">
          Đăng ký ngay
        </a>
      </p>
    </div>
  </div>
</div>

<script>
function togglePass() {
  const pass = document.getElementById("pass");
  const eye = document.getElementById("eye");
  if (pass.type === "password") {
    pass.type = "text";
    eye.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    pass.type = "password";
    eye.classList.replace("fa-eye-slash", "fa-eye");
  }
}
</script>
</body>
</html>