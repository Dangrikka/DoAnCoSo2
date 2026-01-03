<?php
// views/charts.php – FINAL OPTIMIZED 2025
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/SongController.php';

$songCtrl = new SongController();
$topSongs = $songCtrl->charts($_SESSION['user_id'], 20); // Top 20

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid px-4 py-5">

        <div class="d-flex align-items-end gap-4 mb-5 animate-fade-in">
            <div class="chart-header-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div>
                <h6 class="text-uppercase text-muted fw-bold ls-2 mb-1">Thống kê</h6>
                <h1 class="display-3 fw-bolder text-white mb-2" style="text-shadow: 0 0 40px rgba(255,255,255,0.15);">
                    Bảng Xếp Hạng
                </h1>
                <p class="text-white-50 fs-5 mb-0">
                    Top 20 bài hát được nghe nhiều nhất tuần này
                </p>
            </div>
        </div>

        <div class="charts-list">
            <?php if (empty($topSongs)): ?>
                <div class="empty-state text-center py-5">
                    <i class="fas fa-chart-bar fa-6x text-secondary mb-4 opacity-25"></i>
                    <h3 class="text-white fw-bold">Chưa có dữ liệu</h3>
                    <p class="text-muted">Hãy nghe nhạc để kích hoạt bảng xếp hạng!</p>
                </div>
            <?php else: ?>
                
                <div class="chart-header-row text-muted small text-uppercase fw-bold px-4 pb-2 border-bottom border-secondary border-opacity-25 mb-2 d-none d-md-flex">
                    <div style="width: 60px;" class="text-center">#</div>
                    <div class="flex-grow-1">Bài hát</div>
                    <div style="width: 150px;" class="text-end">Lượt nghe</div>
                    <div style="width: 120px;" class="text-center">Thao tác</div>
                </div>

                <?php foreach ($topSongs as $index => $song): 
                    $rank = $index + 1;
                    $plays = number_format($song['play_count'] ?? 0);
                    $isTop3 = $rank <= 3;
                    $rankClass = $isTop3 ? "rank-top rank-$rank" : "rank-normal";
                    
                    // Xử lý ảnh an toàn
                    $imgSrc = !empty($song['image']) ? "../assets/songs/images/" . htmlspecialchars($song['image']) : "../assets/songs/images/default1.jpg";
                    $audioSrc = "../assets/songs/audio/" . htmlspecialchars($song['audio_file']);
                ?>
                    <div class="song-row rounded-4 p-2 mb-2 d-flex align-items-center position-relative transition-all"
                         data-song-id="<?= $song['id'] ?>"
                         data-title="<?= htmlspecialchars($song['title']) ?>"
                         data-artist="<?= htmlspecialchars($song['artist'] ?? 'Unknown') ?>"
                         data-audio="<?= $audioSrc ?>"
                         data-image="<?= $imgSrc ?>"
                         onclick="playSongFromCard(this)"> <div class="chart-rank d-flex justify-content-center align-items-center" style="width: 60px; flex-shrink: 0;">
                            <?php if ($isTop3): ?>
                                <span class="<?= $rankClass ?> fs-3 fw-bold"><?= $rank ?></span>
                            <?php else: ?>
                                <span class="text-muted fs-5 fw-bold"><?= $rank ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center gap-3 flex-grow-1 overflow-hidden">
                            <div class="position-relative flex-shrink-0" style="width: 50px; height: 50px;">
                                <img src="<?= $imgSrc ?>" class="w-100 h-100 object-fit-cover rounded-3 shadow-sm" loading="lazy">
                                <div class="mini-play-overlay">
                                    <i class="fas fa-play text-white"></i>
                                </div>
                            </div>
                            <div class="text-truncate">
                                <h6 class="text-white mb-0 text-truncate fw-bold"><?= htmlspecialchars($song['title']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($song['artist'] ?? 'Unknown') ?></small>
                            </div>
                        </div>

                        <div class="text-end text-white-50 fw-medium d-none d-sm-block" style="width: 150px; flex-shrink: 0;">
                            <?= $plays ?>
                        </div>

                        <div class="chart-actions d-flex justify-content-center gap-2" style="width: 120px; flex-shrink: 0;">
                            <button class="btn-icon btn-like" data-song-id="<?= $song['id'] ?>" onclick="event.stopPropagation()">
                                <i class="fa<?= $song['is_favorite'] ? 's' : 'r' ?> fa-heart <?= $song['is_favorite'] ? 'text-danger' : '' ?>"></i>
                            </button>
                            
                            <button class="btn-icon btn-add" 
                                    data-song-id="<?= $song['id'] ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addToAlbumModal"
                                    onclick="event.stopPropagation()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../includes/player.php'; 
include __DIR__ . '/../includes/footer.php'; 
include __DIR__ . '/../includes/add_to_album_modal.php';
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<style>
/* Header Icon Animation */
.chart-header-icon {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, #FFD700, #FF8C00);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: #fff;
    box-shadow: 0 10px 30px rgba(255, 140, 0, 0.4);
    transform: rotate(-10deg);
}

/* Song Row Styling */
.song-row {
    background: rgba(255, 255, 255, 0.02); /* Rất mờ */
    border: 1px solid transparent;
    cursor: pointer;
}

.song-row:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.1);
}

/* Rank Styling */
.rank-top { 
    text-shadow: 0 0 15px currentColor;
    display: inline-block;
    width: 30px; text-align: center;
}
.rank-1 { color: #FFD700; font-size: 1.8rem !important; } /* Vàng */
.rank-2 { color: #C0C0C0; font-size: 1.6rem !important; } /* Bạc */
.rank-3 { color: #CD7F32; font-size: 1.5rem !important; } /* Đồng */

/* Action Buttons (Nút tròn nhỏ) */
.btn-icon {
    width: 36px; height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.1);
    background: transparent;
    color: #ccc;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: rgba(255,255,255,1);
    color: #000;
    transform: scale(1.1);
}

.btn-like:hover { color: #e74c3c; background: rgba(255,255,255,0.9); }
.btn-like i.text-danger { color: #e74c3c; }

/* Mini Play Overlay trên ảnh nhỏ */
.mini-play-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: 0.2s;
}
.song-row:hover .mini-play-overlay { opacity: 1; }

/* Responsive */
@media (max-width: 576px) {
    .display-3 { font-size: 2.5rem; }
    .chart-header-icon { width: 60px; height: 60px; font-size: 1.8rem; }
    .song-row { padding: 0.5rem; }
}
</style>

<script>
document.addEventListener('click', function(e) {
    // Xử lý nút Like (Delegation)
    const btn = e.target.closest('.btn-like');
    if (btn) {
        const songId = btn.dataset.songId;
        const icon = btn.querySelector('i');

        // Hiệu ứng click ngay lập tức (Optimistic UI)
        const isCurrentlyLiked = icon.classList.contains('fa-solid');
        
        // Toggle UI
        if (isCurrentlyLiked) {
            icon.classList.remove('fa-solid', 'text-danger');
            icon.classList.add('fa-regular');
        } else {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid', 'text-danger');
        }

        // Gửi request ngầm
        fetch('../ajax/favorite_toggle.php', {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "song_id=" + songId
        })
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') {
                // Revert nếu lỗi server
                alert(data.message || "Lỗi server");
                if (isCurrentlyLiked) {
                    icon.classList.add('fa-solid', 'text-danger');
                    icon.classList.remove('fa-regular');
                } else {
                    icon.classList.add('fa-regular');
                    icon.classList.remove('fa-solid', 'text-danger');
                }
            }
        })
        .catch(err => console.error(err));
    }
});
</script>