<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../controllers/SongController.php';
$songCtrl = new SongController();

$songs = $songCtrl->index($_SESSION['user_id']);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <!-- WELCOME SECTION -->
    <div class="welcome-section mb-5 mt-4 text-center text-md-start px-3">
        <h1 class="text-gradient display-3 fw-bold mb-3" 
            style="font-size: 4.8rem; text-shadow: 0 0 60px rgba(0,212,255,0.6);">
            Xin chào, <span class="text-cyan"><?= htmlspecialchars($_SESSION['username']) ?>!</span>
        </h1>
        <p class="lead fs-2 text-muted opacity-90">
            Khám phá những bản hit <span class="text-primary fw-bold">HOT NHẤT</span> hôm nay
        </p>
    </div>

    <!-- DANH SÁCH BÀI HÁT -->
    <?php if (empty($songs)): ?>
        <div class="text-center py-5 my-5">
            <i class="fas fa-compact-disc fa-7x text-primary mb-4 opacity-20"></i>
            <h2 class="text-muted fw-light">Chưa có bài hát nào</h2>
            <p class="text-muted fs-4">Hãy thêm nhạc để bắt đầu hành trình âm nhạc!</p>
        </div>
    <?php else: ?>
        <div class="song-grid">
            <?php foreach ($songs as $song): 
                $isFavorite = !empty($song['is_favorite']);
                $audioUrl = '../assets/songs/audio/' . basename($song['audio_url'] ?? $song['audio_file'] ?? '');
                $imageUrl = !empty($song['image_url']) 
                    ? '../assets/songs/images/' . basename($song['image_url'])
                    : (!empty($song['image']) 
                        ? '../assets/songs/images/' . basename($song['image'])
                        : '../assets/songs/images/default.jpg');
            ?>
                <div class="song-item">
                    <div class="song-card"
                         data-song-id="<?= $song['id'] ?>"
                         data-title="<?= htmlspecialchars($song['title']) ?>"
                         data-artist="<?= htmlspecialchars($song['artist'] ?? 'Nghệ sĩ không rõ') ?>"
                         data-audio="<?= $audioUrl ?>"
                         data-image="<?= $imageUrl ?>"
                         data-is-favorite="<?= $isFavorite ? 'true' : 'false' ?>">

                        <!-- ẢNH BÌA + HOVER -->
                        <div class="card-img-wrapper">
                            <img src="<?= $imageUrl ?>"
                                 class="song-img"
                                 alt="<?= htmlspecialchars($song['title']) ?>"
                                 onerror="this.src='assets/songs/images/default.jpg'"
                                 loading="lazy">

                            <!-- PLAY ICON -->
                            <div class="play-overlay">
                                <i class="fas fa-play-circle fa-5x text-white"></i>
                            </div>

                            <!-- NÚT YÊU THÍCH -->
                            <button class="btn-favorite <?= $isFavorite ? 'active' : '' ?>"
                                    data-song-id="<?= $song['id'] ?>"
                                    onclick="event.stopPropagation(); toggleFavorite(this, <?= $song['id'] ?>)"
                                    title="<?= $isFavorite ? 'Bỏ yêu thích' : 'Yêu thích' ?>">
                                <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                            </button>

                            <!-- NÚT + THÊM VÀO ALBUM -->
                            <button class="btn-add-to-album"
                                    data-song-id="<?= $song['id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addToAlbumModal"
                                    onclick="event.stopPropagation(); window.currentSongId = <?= $song['id'] ?>"
                                    title="Thêm vào album">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>

                        <!-- THÔNG TIN -->
                        <div class="song-info">
                            <h6 class="song-title text-white mb-1 text-truncate">
                                <?= htmlspecialchars($song['title']) ?>
                            </h6>
                            <p class="song-artist text-muted mb-0 text-truncate">
                                <?= htmlspecialchars($song['artist'] ?? 'Nghệ sĩ không rõ') ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL THÊM VÀO ALBUM (chỉ include 1 lần) -->
<?php include '../includes/add_to_album_modal.php'; ?>

<!-- PLAYER + FOOTER -->
<?php 
include '../includes/player.php'; 
include '../includes/footer.php'; 
?>

<!-- CSS SIÊU ĐẸP 2025 -->
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.song-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 2.2rem;
    padding: 2rem 1rem;
    justify-items: center;
}
.song-item { width: 100%; max-width: 280px; }

.song-card {
    background: linear-gradient(145deg, rgba(20,20,40,0.98), rgba(10,10,30,0.95));
    border-radius: 24px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    backdrop-filter: blur(16px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.card-img-wrapper { position: relative; height: 280px; overflow: hidden; }
.song-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.9s ease; }

.play-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 30%, rgba(0,0,0,0.95) 100%);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.5s ease;
}
.play-overlay i { color: white; filter: drop-shadow(0 0 40px black); transform: translateY(30px); transition: all 0.5s ease; }

.song-card:hover .play-overlay { opacity: 1; }
.song-card:hover .play-overlay i { transform: translateY(0); }
.song-card:hover .song-img { transform: scale(1.3); }
.song-card:hover { transform: translateY(-25px) scale(1.06); border-color: #00D4FF; box-shadow: 0 50px 120px rgba(0,212,255,0.8); }

.btn-favorite {
    position: absolute; top: 14px; right: 14px;
    background: rgba(0,0,0,0.75); border: none; border-radius: 50%;
    width: 52px; height: 52px; color: white; font-size: 1.5rem; z-index: 10;
    transition: all 0.4s ease; backdrop-filter: blur(10px);
}
.btn-favorite:hover, .btn-favorite.active { background: #e74c3c; transform: scale(1.25); box-shadow: 0 0 30px rgba(231,76,60,0.8); }

.btn-add-to-album {
    position: absolute; bottom: 14px; left: 14px;
    background: rgba(0,0,0,0.8); border: none; border-radius: 50%;
    width: 52px; height: 52px; color: white; font-size: 1.6rem; z-index: 10;
    opacity: 0; transform: scale(0.7);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    backdrop-filter: blur(12px);
}
.song-card:hover .btn-add-to-album { opacity: 1; transform: scale(1); }
.btn-add-to-album:hover { background: #00D4FF; transform: scale(1.35); box-shadow: 0 0 60px rgba(0,212,255,0.9); }

.song-info { padding: 1.4rem; text-align: center; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent); }
.song-title { font-size: 1.1rem; font-weight: 700; }
.song-artist { font-size: 0.95rem; }

/* RESPONSIVE */
@media (max-width: 1400px) { .song-grid { grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); } }
@media (max-width: 992px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); } }
@media (max-width: 768px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1.8rem; } }
@media (max-width: 576px)  { 
    .song-grid { grid-template-columns: repeat(2, 1fr); gap: 1.4rem; padding: 1rem; }
    .welcome-section h1 { font-size: 3.5rem !important; }
}
</style>

<!-- BIẾN TOÀN CỤC CHO MODAL -->
<script>
// Lưu song_id khi bấm nút "+"
window.currentSongId = null;
</script>

<script src="../assets/js/script.js"></script>