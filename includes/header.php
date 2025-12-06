<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NHẠC HAY <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></title>

  <!-- CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">

  <!-- CSS CHUNG -->
  <link rel="stylesheet" href="assets/css/style.css">

  
  <style>
    :root {
      --primary: #00D4FF;
      --primary-dark: #00A8E8;
      --accent: #9D4EDD;
    }
    body { background: #000; color: white; font-family: 'Poppins', sans-serif; overflow-x: hidden; }
    .text-gradient { 
      background: linear-gradient(90deg, #00D4FF, #9D4EDD); 
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; 
      background-clip: text; 
    }
  </style>
</head>
<body>