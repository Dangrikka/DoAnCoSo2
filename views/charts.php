<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/SongController.php';

$songCtrl = new SongController();
$topSongs = $songCtrl->getTopPlayed($_SESSION['user_id'], 20); 

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid px-4 py-5">

        <!-- HEADER SIÊU ĐỈNH CAO -->
        <div class="charts-header mb-5 d-flex align-items-center gap-4">
            <div class="charts-icon">
                <i class="fas fa-fire text-danger"></i>
            </div>
            <div>
                <h1 class="display-1 fw-bolder text-gradient mb-2" style="font-size:5.5rem;">
                    Bảng Xếp Hạng
                </h1>
                <p class="fs-3 text-white-50 mb-0">
                    <i class="fas fa-headphones me-2"></i>
                    Top 20 bài hát đang được nghe nhiều nhất
                </p>
            </div>
        </div>

        <!-- DANH SÁCH BÀI HÁT XẾP HẠNG -->
        <div class="charts-list">
            <?php if (empty($topSongs)): ?>
                <div class="text-center py-5 my-5">
                    <i class="fas fa-headphones fa-6x text-primary mb-4" style="opacity: 0.2;"></i>
                    <h3 class="text-muted fw-light">Chưa có dữ liệu lượt nghe</h3>
                    <p class="text-muted">Hãy bắt đầu nghe nhạc để tạo bảng xếp hạng nhé!</p>
                </div>
            <?php else: ?>
                <?php foreach ($topSongs as $index => $song): 
                    $plays = number_format($song['play_count'] ?? 0);
                    $isTop3 = $index < 3;
                ?>
                    <div class="song-row song-card position-relative overflow-hidden rounded-4 mb-3 p-3" 
                         style="background:rgba(30,30,50,0.6);border:1px solid rgba(0,212,255,0.2);"
                         data-song-id="<?= $song['id'] ?>"
                         data-title="<?= htmlspecialchars($song['title']) ?>"
                         data-artist="<?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?>"
                         data-audio="../assets/songs/audio/<?= htmlspecialchars($song['audio_file']) ?>"
                         data-image="../assets/songs/images<?= htmlspecialchars($song['image'] ?? 'default.jpg') ?>"
                         onclick="playSongFromCard(this)">

                        <!-- XẾP HẠNG VỚI HUY HIỆU VÀNG/BẠC/ĐỒNG -->
                        <div class="song-rank d-flex align-items-center justify-content-center">
                            <?php if ($isTop3): ?>
                                <div class="rank-medal rank-<?= $index + 1 ?>">

                                    <i class="fas fa-medal"></i>
                                </div>
                            <?php else: ?>
                                <span class="rank-number"><?= $index + 1 ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="song-info d-flex align-items-center gap-3 flex-grow-1">
                            <img src="../assets/songs/images/<?= htmlspecialchars($song['image'] ?? 'default.jpg') ?>" 
                                 class="song-thumb rounded shadow" 
                                 onerror="this.src='../assets/songs/images/default.jpg'">
                            <div>
                                <div class="song-title fw-bold text-white fs-5">
                                    <?= htmlspecialchars($song['title']) ?>
                                </div>
                                <div class="song-artist text-muted">
                                    <?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?>
                                </div>
                            </div>
                        </div>

                        <!-- LƯỢT NGHE -->
                        <div class="song-plays text-center">
                            <i class="fas fa-headphones text-primary me-2"></i>
                            <span class="fw-bold text-white"><?= $plays ?></span>
                            <small class="text-muted d-block">lượt nghe</small>
                        </div>

                        <!-- NÚT HÀNH ĐỘNG -->
                        <div class="song-actions d-flex align-items-center gap-3">
                            <button class="btn-like <?= ($song['is_favorite'] ?? 0) ? 'active' : '' ?>" 
                                    data-song-id="<?= $song['id'] ?>" 
                                    onclick="event.stopPropagation(); toggleFavorite(this);">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="btn-add-to-album" 
                                    data-song-id="<?= $song['id'] ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#addToAlbumModal"
                                    onclick="event.stopPropagation();">
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="play-icon">
                                <i class="fas fa-play fa-2x"></i>
                            </div>
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
.charts-header .charts-icon {
    font-size: 7rem;
    font-weight: 900;
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 30%, #f39c12 70%, #e74c3c 100%);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent; 

    animation: pulseGlow 3s ease-in-out infinite, gradientFlow 8s ease infinite;

    display: inline-block;
    text-shadow: 0 0 30px rgba(255, 107, 107, 0.4);
}

/* HIỆU ỨNG ĐẬP NHẸ (PULSE) */
@keyframes pulseGlow {
    0%, 100% {
        transform: scale(1);
        filter: drop-shadow(0 0 20px rgba(255, 107, 107, 0.5));
    }
    50% {
        transform: scale(1.05);
        filter: drop-shadow(0 0 50px rgba(238, 90, 36, 0.9));
    }
}

/* HIỆU ỨNG GRADIENT CHẢY MƯỢT */
@keyframes gradientFlow {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.7; } }

.song-row {
    display: grid;
    grid-template-columns: 80px 1fr 180px 150px;
    gap: 1rem;
    transition: all 0.4s ease;
    cursor: pointer;
}
.song-row:hover {
    background: rgba(0,212,255,0.15) !important;
    transform: translateY(-5px) scale(1.01);
    box-shadow: 0 20px 40px rgba(0,212,255,0.2);
}

.rank-medal {
    width: 60px; height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    animation: bounce 2s infinite;
}
.rank-1 { background: linear-gradient(135deg, #ffd700, #ffb347); box-shadow: 0 0 30px #ffd700; }
.rank-2 { background: linear-gradient(135deg, #c0c0c0, #a9a9a9); box-shadow: 0 0 30px #c0c0c0; }
.rank-3 { background: linear-gradient(135deg, #cd7f32, #b87333); box-shadow: 0 0 30px #cd7f32; }
@keyframes bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

.rank-number { font-size: 2.2rem; font-weight: 900; color: #aaa; }

.song-thumb { width: 70px; height: 70px; object-fit: cover; }

.song-plays { font-size: 1.1rem; }

.song-actions { opacity: 0; transition: all 0.4s ease; }
.song-row:hover .song-actions { opacity: 1; }

.play-icon {
    width: 60px; height: 60px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0);
    transition: all 0.4s ease;
}
.song-row:hover .play-icon {
    opacity: 1;
    transform: scale(1);
}

.btn-like, .btn-add-to-album {
    width: 45px; height: 45px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}
.btn-like.active, .btn-like:hover { background: #e74c3c; transform: scale(1.2); }
.btn-add-to-album:hover { background: #00D4FF; transform: scale(1.2); }
</style>