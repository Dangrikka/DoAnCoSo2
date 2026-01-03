<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title>ĐR • <?php echo htmlspecialchars($_SESSION['username'] ?? 'Nhạc Hay'); ?></title>

  <link rel="icon" href="../assets/songs/images/logo.jpg" type="image/jpg">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@700;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/style.css">

  <style>
    /* CSS Biến cục bộ & Nền tảng */
    :root {
      --primary: #00D4FF;
      --secondary: #9D4EDD;
      --bg-dark: #0f0c29;
    }
    
    body { 
        background-color: var(--bg-dark); 
        color: white; 
        font-family: 'Inter', sans-serif; 
        overflow-x: hidden; /* Tránh thanh cuộn ngang */
    }

    /* Class text gradient dùng chung */
    .text-gradient { 
      background: linear-gradient(135deg, var(--primary), var(--secondary)); 
      -webkit-background-clip: text; 
      background-clip: text;
      -webkit-text-fill-color: transparent; 
      color: transparent;
      display: inline-block;
    }

    /* Style cho nút Mobile Menu (chỉ hiện ở màn hình nhỏ) */
    .mobile-toggle-btn {
        width: 45px; height: 45px;
        display: flex; align-items: center; justify-content: center;
        z-index: 1050; /* Luôn nổi lên trên */
        transition: transform 0.2s;
    }
    .mobile-toggle-btn:active { transform: scale(0.95); }
  </style>
</head>
<body>

  <button id="mobileSidebarToggle" 
          class="btn btn-dark rounded-circle shadow-lg position-fixed top-0 start-0 m-3 d-lg-none mobile-toggle-btn">
      <i class="fas fa-bars fa-lg"></i>
  </button>