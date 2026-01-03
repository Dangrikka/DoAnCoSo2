<?php
// 1. Khởi động session & Include file cấu hình
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../controllers/SongController.php';

// 2. Kiểm tra quyền Admin/Staff
check_role(['admin', 'staff']);

// 3. Khởi tạo Controller & Lấy dữ liệu thống kê
$songCtrl = new SongController();

// Lấy dữ liệu: ['totalSongs', 'totalPlays', 'totalArtists', 'topSongs']
$stats = $songCtrl->getStatistics();

$totalSongs   = $stats['totalSongs'];
$totalPlays   = $stats['totalPlays'];
$totalArtists = $stats['totalArtists'];
$topSongs     = $stats['topSongs'];

// 4. Chuẩn bị dữ liệu cho Chart.js (Biểu đồ)
$chartLabels = [];
$chartData = [];

// Xử lý dữ liệu cho biểu đồ
if (!empty($topSongs)) {
    foreach ($topSongs as $s) {
        // Cắt tên nếu quá dài để hiển thị dưới cột cho đẹp
        $displayTitle = (mb_strlen($s['title']) > 15) ? mb_substr($s['title'], 0, 15) . '...' : $s['title'];
        $chartLabels[] = $displayTitle;
        $chartData[]   = (int)$s['play_count'];
    }
}

// Include Header chung
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Thống kê</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <style>
        /* --- GLOBAL STYLES --- */
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            font-family: 'Inter', sans-serif;
            color: #fff;
            padding: 30px;
            min-height: 100vh;
            margin: 0;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        /* --- HEADER --- */
        .header-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .title { margin: 0; font-size: 2rem; font-weight: 800; }
        .text-gradient {
            background: linear-gradient(135deg, #00D4FF, #9D4EDD);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-back {
            text-decoration: none;
            color: #a0a0b0;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255,255,255,0.05);
            border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }
        .btn-back:hover { 
            background: rgba(255,255,255,0.1); 
            color: white; 
            transform: translateX(-5px); 
            border-color: #00D4FF;
        }

        /* --- STAT CARDS --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(20, 20, 40, 0.6);
            backdrop-filter: blur(10px);
            padding: 25px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); }

        .stat-info h3 { font-size: 2.5rem; margin: 0; font-weight: 800; }
        .stat-info p { margin: 5px 0 0; color: #a0a0b0; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        
        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
        }
        
        .card-songs .stat-icon { background: rgba(0, 212, 255, 0.15); color: #00D4FF; }
        .card-plays .stat-icon { background: rgba(157, 78, 221, 0.15); color: #9D4EDD; }
        .card-artists .stat-icon { background: rgba(46, 204, 113, 0.15); color: #2ecc71; }

        /* --- DASHBOARD SPLIT --- */
        .dashboard-split {
            display: grid;
            grid-template-columns: 65% 33%; /* Cột biểu đồ rộng hơn */
            gap: 2%;
        }

        .panel {
            background: rgba(20, 20, 40, 0.8);
            padding: 25px;
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
        }
        .panel h4 { 
            margin-top: 0; 
            margin-bottom: 25px; 
            font-size: 1.2rem; 
            color: #fff; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
        }

        /* --- CHART CANVAS WRAPPER --- */
        .chart-container {
            position: relative;
            height: 350px; /* Chiều cao cố định cho biểu đồ */
            width: 100%;
        }

        /* --- TOP LIST --- */
        .top-list { list-style: none; padding: 0; margin: 0; }
        .top-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: 0.2s;
        }
        .top-item:last-child { border-bottom: none; }
        .top-item:hover { background: rgba(255,255,255,0.03); padding-left: 10px; padding-right: 10px; border-radius: 8px; }
        
        .rank { 
            width: 28px; height: 28px; 
            background: #333; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 0.85rem; font-weight: bold; margin-right: 15px;
            flex-shrink: 0;
        }
        .rank-1 { background: linear-gradient(135deg, #f1c40f, #f39c12); color: #000; box-shadow: 0 0 10px rgba(241, 196, 15, 0.5); } 
        .rank-2 { background: linear-gradient(135deg, #bdc3c7, #95a5a6); color: #000; } 
        .rank-3 { background: linear-gradient(135deg, #e67e22, #d35400); color: #fff; } 

        .song-info-wrapper { display: flex; align-items: center; flex-grow: 1; overflow: hidden; }
        .song-name { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px; }
        
        .play-count-badge { 
            color: #00D4FF; font-weight: 700; font-size: 0.9rem; 
            background: rgba(0, 212, 255, 0.1); padding: 5px 10px; border-radius: 8px;
        }

        @media (max-width: 992px) {
            .dashboard-split { grid-template-columns: 1fr; gap: 30px; }
        }
        @media (max-width: 576px) {
            .header-nav { flex-direction: column; gap: 15px; align-items: flex-start; }
            .stat-info h3 { font-size: 2rem; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header-nav">
        <div>
            <h2 class="title text-gradient"><i class="fa-solid fa-chart-pie"></i> DASHBOARD</h2>
            <p style="margin: 5px 0 0; color: #a0a0b0; font-size: 0.9rem;">Tổng quan thư viện nhạc</p>
        </div>
        <a href="../views/home.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Về Trang chủ
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card card-songs">
            <div class="stat-info">
                <h3><?= number_format($totalSongs) ?></h3>
                <p>Bài hát</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-music"></i></div>
        </div>
        <div class="stat-card card-plays">
            <div class="stat-info">
                <h3><?= number_format($totalPlays) ?></h3>
                <p>Lượt nghe</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-headphones-simple"></i></div>
        </div>
        <div class="stat-card card-artists">
            <div class="stat-info">
                <h3><?= number_format($totalArtists) ?></h3>
                <p>Ca sĩ</p>
            </div>
            <div class="stat-icon"><i class="fa-solid fa-microphone-lines"></i></div>
        </div>
    </div>

    <div class="dashboard-split">
        
        <div class="panel">
            <h4><i class="fa-solid fa-chart-simple"></i> Top Bài Hát Trending (Biểu đồ cột)</h4>
            <div class="chart-container">
                <canvas id="topSongChart"></canvas>
            </div>
            <?php if (empty($chartData) || array_sum($chartData) == 0): ?>
            <div style="margin-top:12px; color:#aaa; text-align:center;">
                Chưa có dữ liệu để hiển thị biểu đồ
            </div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <h4><i class="fa-solid fa-trophy"></i> Bảng Xếp Hạng</h4>
            <ul class="top-list">
                <?php if(empty($topSongs)): ?>
                    <li class="top-item" style="justify-content:center; color:#aaa; padding: 30px 0;">
                        Chưa có dữ liệu thống kê
                    </li>
                <?php else: ?>
                    <?php 
                        $rank = 1;
                        foreach($topSongs as $song): 
                            $rankClass = ($rank <= 3) ? "rank-$rank" : "";
                    ?>
                        <li class="top-item">
                            <div class="song-info-wrapper">
                                <div class="rank <?= $rankClass ?>"><?= $rank++ ?></div>
                                <span class="song-name" title="<?= htmlspecialchars($song['title']) ?>">
                                    <?= htmlspecialchars($song['title']) ?>
                                </span>
                            </div>
                            <span class="play-count-badge">
                                <?= number_format($song['play_count']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. NHẬN DỮ LIỆU TỪ PHP (An toàn hơn cách dùng attributes)
        // json_encode sẽ chuyển mảng PHP thành mảng Javascript chuẩn
        const labels = <?php echo json_encode($chartLabels); ?>;
        const dataPoints = <?php echo json_encode($chartData); ?>;

        // Nếu không có dữ liệu thì không vẽ
        if (!dataPoints || dataPoints.length === 0) {
            console.info('Không có dữ liệu biểu đồ');
            return;
        }

        const ctx = document.getElementById('topSongChart').getContext('2d');

        // Tạo màu gradient cho cột (Từ xanh sang tím)
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#00D4FF'); // Màu đỉnh cột
        gradient.addColorStop(1, '#9D4EDD'); // Màu chân cột

        // 2. CẤU HÌNH CHART.JS
        new Chart(ctx, {
            type: 'bar', // 'bar' mặc định là biểu đồ cột dọc
            data: {
                labels: labels, // Tên các bài hát
                datasets: [{
                    label: 'Lượt nghe',
                    data: dataPoints, // Số lượt nghe tương ứng
                    backgroundColor: gradient, // Màu cột
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    borderRadius: 5, // Bo tròn góc trên của cột
                    barPercentage: 0.6, // Độ rộng của cột (0.6 = 60%)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Để biểu đồ tự co giãn theo khung div
                plugins: {
                    legend: {
                        display: false // Ẩn chú thích vì chỉ có 1 loại dữ liệu
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 12, 41, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#00D4FF',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return ' Lượt nghe: ' + new Intl.NumberFormat().format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)', // Màu kẻ ngang mờ
                            borderDash: [5, 5]
                        },
                        ticks: {
                            color: '#a0a0b0',
                            font: { size: 11 }
                        },
                        title: {
                            display: true,
                            text: 'Số lượt nghe',
                            color: '#a0a0b0',
                            font: { size: 10 }
                        }
                    },
                    x: {
                        grid: {
                            display: false // Ẩn kẻ dọc
                        },
                        ticks: {
                            color: '#fff',
                            font: { weight: '600', size: 11 }
                        }
                    }
                }
            }
        });
    });
</script>

</body>
</html>