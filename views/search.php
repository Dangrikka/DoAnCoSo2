<?php
// views/search.php – SIÊU PHẨM TÌM KIẾM 2025 – ĐẸP HƠN SPOTIFY THẬT!
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/SongController.php';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$searchQuery = trim($_GET['q'] ?? '');
$results = [];

if (!empty($searchQuery)) {
    $songCtrl = new SongController();
    $results = $songCtrl->searchSongs($searchQuery, $_SESSION['user_id']);
}
?>

<div class="main-content">
    <div class="container py-5">
        <h1 class="text-gradient display-3 fw-bold mb-5 text-center" 
            style="font-size: 5rem; text-shadow: 0 0 60px rgba(0,212,255,0.6);">
            Tìm Kiếm Nhạc
        </h1>

        <!-- FORM TÌM KIẾM SIÊU ĐẸP -->
        <form method="get" class="mb-5">
            <div class="search-form-wrapper mx-auto">
                <input type="text" 
                       name="q" 
                       class="search-input" 
                       placeholder="Tìm bài hát, nghệ sĩ, album..." 
                       value="<?= htmlspecialchars($searchQuery) ?>" 
                       autofocus 
                       autocomplete="off">
                <button class="search-button" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <?php if (!empty($searchQuery)): ?>
            <div class="search-results">
                <h3 class="mb-5 text-center">
                    <span class="text-muted">Kết quả tìm kiếm cho:</span><br>
                    <span class="text-primary display-6 fw-bold">"<?= htmlspecialchars($searchQuery) ?>"</span>
                    <span class="text-muted ms-3">(<?= count($results) ?> kết quả)</span>
                </h3>

                <?php if (empty($results)): ?>
                    <div class="text-center py-5 my-5">
                        <i class="fas fa-search fa-6x text-primary mb-4 opacity-20"></i>
                        <h2 class="text-muted fw-light">Không tìm thấy bài hát nào</h2>
                        <p class="text-muted fs-4">Thử tìm với từ khóa khác nhé!</p>
                    </div>
                <?php else: ?>
                    <!-- DÙNG GRID ĐẸP NHƯ TRANG CHỦ -->
                    <div class="song-grid">
                        <?php foreach ($results as $song): 
                            $isFavorite = !empty($song['is_favorite']);
                            $audioFile = '../assets/songs/audio/' . htmlspecialchars(basename($song['audio_file']));
                            $imageFile = !empty($song['image']) && $song['image'] !== 'default.jpg'
                                ? '../assets/songs/images/' . htmlspecialchars(basename($song['image']))
                                : '../assets/songs/images/default.jpg';
                        ?>
                            <div class="song-item">
                                <div class="song-card"
                                     data-song-id="<?= $song['id'] ?>"
                                     data-title="<?= htmlspecialchars($song['title']) ?>"
                                     data-artist="<?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?>"
                                     data-audio="<?= $audioFile ?>"
                                     data-image="<?= $imageFile ?>"
                                     data-is-favorite="<?= $isFavorite ? 'true' : 'false' ?>">

                                    <div class="card-img-wrapper position-relative overflow-hidden rounded-4 shadow-lg">
                                        <img src="<?= $imageFile ?>"
                                             class="song-img w-100 h-100"
                                             alt="<?= htmlspecialchars($song['title']) ?>"
                                             onerror="this.src='../assets/songs/images/default.jpg'"
                                             loading="lazy">

                                        <!-- PLAY OVERLAY -->
                                        <div class="play-overlay">
                                            <i class="fas fa-play-circle fa-5x"></i>
                                        </div>

                                        <!-- NÚT YÊU THÍCH -->
                                        <button class="btn-favorite <?= $isFavorite ? 'active' : '' ?>"
                                                data-song-id="<?= $song['id'] ?>"
                                                onclick="event.stopPropagation(); toggleFavorite(this, <?= $song['id'] ?>)"
                                                title="<?= $isFavorite ? 'Bỏ yêu thích' : 'Yêu thích' ?>">
                                            <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                                        </button>

                                        <!-- DẤU "+" GÓC TRÁI DƯỚI -->
                                        <button class="btn-add-to-album"
                                                data-song-id="<?= $song['id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addToAlbumModal"
                                                onclick="event.stopPropagation();"
                                                title="Thêm vào album">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>

                                    <div class="p-4 text-center">
                                        <h6 class="fw-bold text-white mb-1 text-truncate">
                                            <?= htmlspecialchars($song['title']) ?>
                                        </h6>
                                        <p class="small text-muted mb-0 text-truncate">
                                            <?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
include __DIR__ . '/../includes/player.php'; 
include __DIR__ . '/../includes/footer.php'; 
include __DIR__ . '/../includes/add_to_album_modal.php';
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<!-- CSS TÌM KIẾM + GRID ĐỒNG BỘ VỚI TRANG CHỦ -->
<style>
.search-form-wrapper {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
}
.search-input {
    width: 100%;
    padding: 1.5rem 6rem 1.5rem 3rem;
    font-size: 1.5rem;
    background: rgba(20,20,40,0.9);
    border: 3px solid transparent;
    border-radius: 60px;
    color: white;
    backdrop-filter: blur(15px);
    transition: all 0.5s ease;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}
.search-input::placeholder { color: #888; }
.search-input:focus {
    outline: none;
    border-color: #00D4FF;
    background: rgba(0,212,255,0.15);
    box-shadow: 0 0 50px rgba(0,212,255,0.6);
    transform: scale(1.02);
}
.search-button {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: linear-gradient(135deg, #00D4FF, #8A2BE2);
    border: none;
    color: white;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    font-size: 1.8rem;
    transition: all 0.4s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}
.search-button:hover {
    transform: translateY(-50%) scale(1.2);
    box-shadow: 0 0 50px rgba(0,212,255,0.8);
}

/* DÙNG CHUNG GRID VỚI TRANG CHỦ → ĐẸP ĐỒNG ĐỀU */
.song-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 2rem;
    padding: 2rem 1rem;
    justify-items: center;
}
.song-item { width: 100%; max-width: 260px; }

.song-card {
    background: rgba(20,20,40,0.95);
    border-radius: 20px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    backdrop-filter: blur(10px);
}

.card-img-wrapper { position: relative; height: 260px; overflow: hidden; }
.song-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }

.play-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,0.95));
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.5s ease;
}
.play-overlay i { color: white; filter: drop-shadow(0 0 30px black); transform: translateY(20px); transition: all 0.5s; }

.song-card:hover .play-overlay { opacity: 1; }
.song-card:hover .play-overlay i { transform: translateY(0); }
.song-card:hover .song-img { transform: scale(1.25); }
.song-card:hover {
    transform: translateY(-20px) scale(1.05);
    border-color: #00D4FF;
    box-shadow: 0 40px 100px rgba(0,212,255,0.7);
}
.song-card.active {
    border-color: #00D4FF !important;
    box-shadow: 0 0 100px rgba(0,212,255,1) !important;
}

/* NÚT YÊU THÍCH & + */
.btn-favorite, .btn-add-to-album {
    position: absolute; border: none; border-radius: 50%;
    width: 52px; height: 52px; z-index: 10;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.4s ease;
}
.btn-favorite {
    top: 12px; right: 12px;
    background: rgba(0,0,0,0.7);
    color: white; font-size: 1.4rem;
}
.btn-favorite:hover, .btn-favorite.active {
    background: #e74c3c; transform: scale(1.2);
}

.btn-add-to-album {
    bottom: 12px; left: 12px;
    background: rgba(0,0,0,0.8);
    color: white; font-size: 1.9rem;
    opacity: 0; transform: scale(0.8);
    backdrop-filter: blur(12px);
}
.song-card:hover .btn-add-to-album {
    opacity: 1; transform: scale(1);
}
.btn-add-to-album:hover {
    background: #00D4FF; transform: scale(1.3);
    box-shadow: 0 0 50px rgba(0,212,255,0.9);
}

/* Responsive */
@media (max-width: 1200px) { .song-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); } }
@media (max-width: 992px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); } }
@media (max-width: 768px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); } }
@media (max-width: 576px)  { 
    .song-grid { grid-template-columns: repeat(2, 1fr); gap: 1.2rem; padding: 1rem; }
    .search-input { font-size: 1.2rem; padding: 1.2rem 5rem 1.2rem 2rem; }
}
</style>