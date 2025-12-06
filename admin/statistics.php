<?php
require_once '../includes/auth.php';
check_role(['admin', 'staff']);

require_once '../controllers/SongController.php';
$songCtrl = new SongController();

// === THỐNG KÊ TỔNG QUAN ===
$totalSongs     = $songCtrl->songModel->conn->query("SELECT COUNT(*) FROM songs")->fetch_row()[0];
$totalPlays     = $songCtrl->songModel->conn->query("SELECT COALESCE(SUM(play_count),0) FROM play_counts")->fetch_row()[0];
$totalUsers     = $songCtrl->songModel->conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$totalFavorites = $songCtrl->songModel->conn->query("SELECT COUNT(*) FROM favorites")->fetch_row()[0];

// === TOP 10 BÀI HÁT ĐƯỢC NGHE NHIỀU NHẤT ===
$topSongs = $songCtrl->songModel->conn->query("
    SELECT s.title, s.artist, COALESCE(pc.play_count,0) as plays
    FROM songs s
    LEFT JOIN play_counts pc ON s.id = pc.song_id
    ORDER BY plays DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// === TOP 10 CA SĨ NHIỀU LƯỢT NGHE NHẤT ===
$topArtists = $songCtrl->songModel->conn->query("
    SELECT s.artist, SUM(COALESCE(pc.play_count,0)) as total_plays
    FROM songs s
    LEFT JOIN play_counts pc ON s.id = pc.song_id
    GROUP BY s.artist
    ORDER BY total_plays DESC LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// === LƯỢT NGHE THEO NGÀY (7 NGÀY GẦN NHẤT) ===
$playHistory = $songCtrl->songModel->conn->query("
    SELECT DATE(last_played) as play_date, SUM(play_count) as daily_plays
    FROM play_counts
    WHERE last_played >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY play_date
    ORDER BY play_date
")->fetch_all(MYSQLI_ASSOC);

// Chuẩn bị dữ liệu cho biểu đồ
$dates = [];
$plays = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d/m', strtotime($date));
    $found = false;
    foreach ($playHistory as $row) {
        if ($row['play_date'] == $date) {
            $plays[] = (int)$row['daily_plays'];
            $found = true;
            break;
        }
    }
    if (!$found) $plays[] = 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Thống kê - MusicVN</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Poppins:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #00D4FF; --secondary: #9D4EDD; --success: #00D084; --warning: #FFB800; }
        body { background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); color: white; font-family: 'Inter', sans-serif; min-height: 100vh; padding: 30px 15px; }
        .container { max-width: 1500px; margin: 0 auto; }
        .text-gradient { font-family: 'Poppins', sans-serif; font-size: 4rem; font-weight: 900; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-align: center; margin-bottom: 3rem; text-shadow: 0 0 60px rgba(0,212,255,0.5); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 50px; }
        .stat-card { background: rgba(20,20,40,0.95); border-radius: 24px; padding: 30px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.4); border: 1px solid rgba(0,212,255,0.2); transition: 0.4s; }
        .stat-card:hover { transform: translateY(-12px); box-shadow: 0 30px 80px rgba(0,212,255,0.4); }
        .stat-card i { font-size: 3.5rem; margin-bottom: 15px; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-card .number { font-size: 3rem; font-weight: 900; margin: 10px 0; }
        .stat-card .label { font-size: 1.2rem; opacity: 0.9; }

        .chart-container { background: rgba(20,20,40,0.95); border-radius: 24px; padding: 30px; margin-bottom: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.5); border: 1px solid rgba(0,212,255,0.2); }
        .chart-title { text-align: center; font-size: 1.8rem; margin-bottom: 20px; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; background: rgba(20,20,40,0.95); border-radius: 20px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.5); margin-bottom: 40px; }
        th { background: rgba(0,212,255,0.2); padding: 18px; font-weight: 700; text-transform: uppercase; font-size: 0.95rem; letter-spacing: 1px; }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        tr:hover { background: rgba(0,212,255,0.1); }
        .rank { font-weight: bold; font-size: 1.4rem; color: var(--primary); }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-gradient">THỐNG KÊ HỆ THỐNG</h1>

    <!-- Tổng quan -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fas fa-music"></i>
            <div class="number"><?= number_format($totalSongs) ?></div>
            <div class="label">Tổng bài hát</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-play-circle"></i>
            <div class="number"><?= number_format($totalPlays) ?></div>
            <div class="label">Tổng lượt nghe</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div class="number"><?= number_format($totalUsers) ?></div>
            <div class="label">Người dùng</div>
        </div>
        <div class="stat-card">
            <i class="fas fa-heart"></i>
            <div class="number"><?= number_format($totalFavorites) ?></div>
            <div class="label">Yêu thích</div>
        </div>
    </div>

    <!-- Biểu đồ lượt nghe 7 ngày -->
    <div class="chart-container">
        <h2 class="chart-title">LƯỢT NGHE 7 NGÀY QUA</h2>
        <canvas id="playChart" height="100"></canvas>
    </div>

    <!-- Top 10 bài hát -->
    <div class="chart-container">
        <h2 class="chart-title">TOP 10 BÀI HÁT HOT NHẤT</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tiêu đề</th>
                    <th>Ca sĩ</th>
                    <th>Lượt nghe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topSongs as $i => $s): ?>
                <tr>
                    <td class="rank"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($s['title']) ?></strong></td>
                    <td><?= htmlspecialchars($s['artist']) ?></td>
                    <td><i class="fas fa-play"></i> <?= number_format($s['plays']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Top 10 ca sĩ -->
    <div class="chart-container">
        <h2 class="chart-title">TOP 10 CA SĨ ĐƯỢC YÊU THÍCH</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên ca sĩ</th>
                    <th>Tổng lượt nghe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topArtists as $i => $a): ?>
                <tr>
                    <td class="rank"><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($a['artist']) ?></strong></td>
                    <td><i class="fas fa-play"></i> <?= number_format($a['total_plays']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mt-5">
        <a href="songs.php" class="btn" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 16px 50px; border-radius: 50px; color:white; text-decoration:none; font-weight:700;">
            ← Quay lại Quản lý bài hát
        </a>
    </div>
</div>

<script>
// Biểu đồ lượt nghe 7 ngày
new Chart(document.getElementById('playChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($dates) ?>,
        datasets: [{
            label: 'Lượt nghe mỗi ngày',
            data: <?= json_encode($plays) ?>,
            borderColor: '#00D4FF',
            backgroundColor: 'rgba(0, 212, 255, 0.2)',
            borderWidth: 4,
            pointBackgroundColor: '#9D4EDD',
            pointRadius: 8,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: 'white', font: { size: 16 } } } },
        scales: {
            y: { ticks: { color: 'white' }, grid: { color: 'rgba(255,255,255,0.1)' } },
            x: { ticks: { color: 'white' }, grid: { color: 'rgba(255,255,255,0.1)' } }
        }
    }
});
</script>

</body>
</html>