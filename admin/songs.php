<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Include các file cần thiết TRƯỚC KHI dùng
require_once '../config/database.php';                    // Kết nối database
require_once '../includes/auth.php';               // Hàm check_role + các hàm bảo mật
require_once '../controllers/SongController.php';

// 3. KIỂM TRA QUYỀN – DÙNG HÀM ĐÃ CÓ TRONG auth.php
check_role(['admin', 'staff']);                     // Nếu không có quyền → tự động die

$songCtrl = new SongController();
$msg = $msgType = '';

// Xử lý xóa
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($songCtrl->delete($id)) {
        $msg = "Xóa bài hát thành công!";
        $msgType = "success";
    } else {
        $msg = "Lỗi khi xóa bài hát!";
        $msgType = "danger";
    }
}

// Lấy danh sách bài hát cho admin
$songs = $songCtrl->getAllAdmin();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Quản lý bài hát - MusicVN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00D4FF;
            --danger: #e74c3c;
            --success: #2ecc71;
        }
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: white;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            padding: 30px 15px;
        }
        .container { max-width: 1400px; margin: 0 auto; }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .text-gradient {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, var(--primary), #9D4EDD);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.4s;
            margin: 0 8px;
        }
        .btn-add {
            background: linear-gradient(135deg, var(--primary), #9D4EDD);
            color: white;
            box-shadow: 0 10px 30px rgba(0,212,255,0.4);
        }
        .btn-add:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,212,255,0.6); }

        .btn-edit { background: #f39c12; color: white; padding: 10px 16px; border-radius: 8px; }
        .btn-delete { background: var(--danger); color: white; padding: 10px 16px; border-radius: 8px; }
        .btn-edit:hover, .btn-delete:hover { transform: scale(1.1); opacity: 0.9; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(20,20,40,0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        th, td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        th {
            background: rgba(0,212,255,0.2);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.95rem;
        }
        tr:hover {
            background: rgba(0,212,255,0.1);
        }
        .cover {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--primary);
        }
        .alert {
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            font-size: 1.3rem;
            margin: 20px 0;
            animation: fadeIn 0.6s;
        }
        .success { background: rgba(46,204,113,0.95); }
        .danger  { background: rgba(231,76,60,0.95); }

        @keyframes fadeIn {
            from { opacity:0; transform:translateY(-20px); }
            to   { opacity:1; transform:none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1 class="text-gradient">QUẢN LÝ BÀI HÁT</h1>
        <a href="upload.php" class="btn btn-add">
            <i class="fas fa-plus-circle"></i> THÊM BÀI HÁT MỚI
        </a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert <?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ảnh bìa</th>
                <th>Tiêu đề</th>
                <th>Ca sĩ</th>
                <th>Lượt nghe</th>
                <th>Thời gian thêm</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($songs)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:60px; color:#aaa; font-size:1.2rem;">
                        <i class="fas fa-music fa-4x mb-3"></i><br>
                        Chưa có bài hát nào trong thư viện
                    </td>
                </tr>
            <?php else: $stt = 1; foreach ($songs as $s): ?>
                <tr>
                    <td><?= $stt++ ?></td>
                    <td>
                        <img src="../assets/songs/images/<?= htmlspecialchars($s['image_url'] ?? 'default.jpg') ?>" 
                             alt="cover" class="cover" onerror="this.src='../assets/songs/images/default.jpg'">
                    </td>
                    <td><strong><?= htmlspecialchars($s['title']) ?></strong></td>
                    <td><?= htmlspecialchars($s['artist']) ?></td>
                    <td><i class="fas fa-play"></i> <?= number_format($s['play_count'] ?? 0) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $s['id'] ?>" class="btn btn-edit" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?delete=<?= $s['id'] ?>" class="btn btn-delete" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa bài hát:\n<?= addslashes(htmlspecialchars($s['title'])) ?>?\nHành động này không thể hoàn tác!')">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>