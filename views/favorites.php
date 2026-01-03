<?php
// views/favorites.php – FINAL COMPLETE VERSION 2025
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php'; 

$user_id = (int)$_SESSION['user_id'];

// 1. LẤY DANH SÁCH YÊU THÍCH TỪ DATABASE
$stmt = $GLOBALS['db_conn']->prepare("
    SELECT s.*, f.created_at as favorited_at 
    FROM songs s 
    JOIN favorites f ON s.id = f.song_id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$favorites = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4 px-lg-5">
        
        <div class="d-flex flex-column flex-md-row align-items-end justify-content-between mb-5 gap-4 animate-fade-in">
            <div class="d-flex align-items-end gap-4">
                <div class="fav-header-icon shadow-lg">
                    <i class="fas fa-heart"></i>
                </div>
                <div>
                    <h6 class="text-uppercase text-muted fw-bold ls-2 mb-2">Playlist</h6>
                    <h1 class="display-3 fw-bolder text-white mb-0" style="text-shadow: 0 0 30px rgba(231,76,60,0.3);">
                        Bài hát yêu thích
                    </h1>
                    <p class="text-white-50 mt-2 fs-5 mb-0">
                        <span class="text-white fw-bold"><?= count($favorites) ?></span> bài hát mà bạn yêu thương
                    </p>
                </div>
            </div>

            <?php if (!empty($favorites)): ?>
            <div class="d-flex gap-3">
                <button class="btn btn-primary rounded-pill px-4 py-3 fw-bold shadow-lg d-flex align-items-center gap-2 hover-scale"
                        onclick="playAllFavorites()">
                    <i class="fas fa-play"></i> Nghe tất cả
                </button>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($favorites)): ?>
        <div class="mb-4 mw-600 position-relative">
            <i class="fas fa-search position-absolute text-muted" style="left: 20px; top: 50%; transform: translateY(-50%);"></i>
            <input type="text" id="favSearch" class="form-control form-control-lg rounded-pill bg-dark border-0 text-white ps-5" 
                   placeholder="Tìm trong danh sách yêu thích..." 
                   onkeyup="filterFavorites()">
        </div>
        <?php endif; ?>

        <?php if (empty($favorites)): ?>
            <div class="empty-state text-center py-5 my-5">
                <div class="mb-4 position-relative d-inline-block">
                    <div class="blob-heart"></div>
                    <i class="fas fa-heart-broken fa-8x text-secondary position-relative z-1 opacity-50"></i>
                </div>
                <h2 class="text-white fw-light mb-3">Chưa có bài hát nào</h2>
                <p class="text-muted fs-5 mb-4">Thả tim <i class="fas fa-heart text-danger"></i> vào bài hát bạn thích để lưu vào đây nhé!</p>
                <a href="home.php" class="btn btn-outline-light rounded-pill px-5 py-2 fw-bold">Khám phá ngay</a>
            </div>
        <?php else: ?>
            <div class="song-grid" id="favoritesGrid">
                <?php foreach ($favorites as $song): 
                    $audioFile = '../assets/songs/audio/' . htmlspecialchars(basename($song['audio_file']));
                    $imageFile = !empty($song['image']) && file_exists('../assets/songs/images/' . basename($song['image']))
                        ? '../assets/songs/images/' . htmlspecialchars(basename($song['image']))
                        : '../assets/songs/images/default1.jpg';
                ?>
                    <div class="song-item" data-search-term="<?= strtolower(htmlspecialchars($song['title'] . ' ' . ($song['artist'] ?? ''))) ?>">
                        <div class="song-card"
                             data-song-id="<?= $song['id'] ?>"
                             data-title="<?= htmlspecialchars($song['title']) ?>"
                             data-artist="<?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?>"
                             data-audio="<?= $audioFile ?>"
                             data-image="<?= $imageFile ?>"
                             data-is-favorite="true"
                             onclick="playSongFromCard(this)">

                            <div class="card-img-wrapper">
                                <img src="<?= $imageFile ?>" 
                                     class="song-img" 
                                     alt="<?= htmlspecialchars($song['title']) ?>"
                                     loading="lazy">
                                
                                <div class="play-overlay">
                                    <i class="fas fa-play-circle fa-4x text-white"></i>
                                </div>

                                <button class="btn-favorite active"
                                        data-song-id="<?= $song['id'] ?>"
                                        onclick="event.stopPropagation(); toggleFavorite(this, <?= $song['id'] ?>)"
                                        title="Bỏ thích">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>

                            <div class="p-3 text-center">
                                <h6 class="fw-bold text-white mb-1 text-truncate"><?= htmlspecialchars($song['title']) ?></h6>
                                <p class="small text-muted mb-0 text-truncate"><?= htmlspecialchars($song['artist'] ?? 'Unknown') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<div id="favoritesData" class="d-none">
    <?= json_encode(array_map(function($s) {
        return [
            'id' => $s['id'],
            'title' => $s['title'],
            'artist' => $s['artist'] ?? 'Unknown',
            'audio' => '../assets/songs/audio/' . basename($s['audio_file']),
            'image' => !empty($s['image']) ? '../assets/songs/images/' . basename($s['image']) : '../assets/songs/images/default1.jpg',
            'isFavorite' => true
        ];
    }, $favorites)); ?>
</div>

<?php 
include '../includes/player.php'; 
include '../includes/footer.php'; 
include '../includes/add_to_album_modal.php';
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<script>
// Filter danh sách yêu thích
function filterFavorites() {
    const input = document.getElementById('favSearch');
    const filter = input.value.toLowerCase();
    const items = document.querySelectorAll('.song-item');

    items.forEach(item => {
        const term = item.getAttribute('data-search-term');
        if (term.includes(filter)) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
}

// Chức năng Play All
function playAllFavorites() {
    try {
        const dataDiv = document.getElementById('favoritesData');
        if (!dataDiv) return;
        
        const songs = JSON.parse(dataDiv.textContent);
        if (songs && songs.length > 0) {
            window.playlist = songs;
            window.playSong(0, true);
        }
    } catch (e) {
        console.error("Lỗi khi phát tất cả:", e);
    }
}
</script>

<style>
/* --- HEADER --- */
.fav-header-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, #ff6b6b, #e74c3c);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 3.5rem; color: white;
    transform: rotate(-5deg);
}

.blob-heart {
    position: absolute; top: 50%; left: 50%;
    width: 150px; height: 150px;
    background: radial-gradient(circle, rgba(231,76,60,0.4) 0%, rgba(231,76,60,0) 70%);
    transform: translate(-50%, -50%);
    animation: pulse 3s infinite;
}

/* --- LAYOUT GRID --- */
.song-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 24px;
    padding-bottom: 50px;
}

.song-card {
    background: rgba(24, 24, 24, 0.6);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    border: 1px solid transparent;
}
.song-card:hover {
    background: rgba(40, 40, 40, 1);
    transform: translateY(-8px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.card-img-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 1/1;
    overflow: hidden;
    border-radius: 8px;
    margin: 12px 12px 0 12px;
    width: calc(100% - 24px); 
    box-shadow: 0 5px 15px rgba(0,0,0,0.5);
}

.song-img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.song-card:hover .song-img { transform: scale(1.08); }

/* Play Overlay (Center) */
.play-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.3s ease;
    backdrop-filter: blur(2px);
}
.play-overlay i { transform: scale(0.8); transition: transform 0.3s; filter: drop-shadow(0 0 10px rgba(0,0,0,0.5)); }
.song-card:hover .play-overlay { opacity: 1; }
.song-card:hover .play-overlay i { transform: scale(1); }


/* --- BUTTONS POSITIONING --- */
.btn-favorite {
    position: absolute;
    width: 36px; height: 36px;
    border-radius: 50%;
    border: none;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 10;
    opacity: 0;
}

/* GÓC TRÊN - PHẢI (Tim) */
.btn-favorite { 
    top: 10px; right: 10px; 
    background: rgba(0,0,0,0.6); 
    color: #e74c3c; 
    font-size: 1.1rem;
    transform: translateY(-10px);
}


/* Hover Effects */
.song-card:hover .btn-favorite, 
.song-card:hover .btn-add-to-album { 
    opacity: 1; transform: translateY(0); 
}

.btn-favorite:hover { background: #e74c3c; color: white; transform: scale(1.15) !important; }

/* Input Search */
#favSearch::placeholder { color: rgba(255,255,255,0.4); }
#favSearch:focus { box-shadow: 0 0 0 2px rgba(231,76,60,0.5); background: #222 !important; }

/* Responsive */
@media (max-width: 576px) {
    .song-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .display-3 { font-size: 2.5rem; }
    .fav-header-icon { width: 60px; height: 60px; font-size: 2rem; }
}
</style>