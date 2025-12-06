<?php
// views/favorites.php – SIÊU PHẨM TRANG YÊU THÍCH 2025 – ĐẸP NHẤT THẾ GIỚI!
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php'; 

$user_id = (int)$_SESSION['user_id'];

// LẤY DANH SÁCH YÊU THÍCH – ĐÃ TỐI ƯU HOÀN HẢO
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
    <div class="container-fluid px-4">
        <div class="text-center text-md-start mb-5">
            <h1 class="text-gradient display-2 fw-bold mb-3 position-relative d-inline-block">
                <i class="fas fa-heart text-danger me-4" 
                 style="font-size: 6.5rem;
                       background: linear-gradient(135deg, #ff6b6b, #e74c3c, #c0392b);
                      -webkit-background-clip: text;
                      -webkit-text-fill-color: transparent;
                       background-clip: text;
                       color: transparent;
                       filter: drop-shadow(0 0 40px rgba(231,76,60,0.7))
                       drop-shadow(0 0 80px rgba(231,76,60,0.4));
                       text-shadow: 0 0 30px rgba(231,76,60,0.6);">
                </i>
                Bài hát yêu thích của bạn
            </h1>
            <p class="text-muted fs-3 opacity-90 mt-3">
                Bạn đang có <span class="text-danger fw-bold"><?= count($favorites) ?></span> 
                bản nhạc làm trái tim bạn rung động
            </p>
        </div>

        <!-- CHƯA CÓ BÀI NÀO -->
        <?php if (empty($favorites)): ?>
            <div class="text-center py-5 my-5">
                <i class="fas fa-heart-broken fa-10x text-muted mb-5 opacity-15"
                   style="filter: drop-shadow(0 0 40px rgba(0,0,0,0.3));"></i>
                <h2 class="text-muted fw-light mb-4">Trái tim bạn đang trống rỗng...</h2>
                <p class="text-muted fs-4 mb-5">Hãy khám phá và thêm những bản nhạc làm bạn rung động!</p>
                <a href="home.php" class="btn btn-primary btn-lg px-6 py-4 rounded-pill shadow-lg fs-4">
                    <i class="fas fa-home me-3"></i> Về trang chủ tìm nhạc
                </a>
            </div>

        <!-- CÓ BÀI HÁT YÊU THÍCH -->
        <?php else: ?>
            <div class="song-grid">
                <?php foreach ($favorites as $song): 
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
                             data-is-favorite="true">

                            <!-- Ảnh + hiệu ứng -->
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

                                <!-- TRÁI TIM ĐỎ RỰC – LUÔN ACTIVE -->
                                <button class="btn-favorite active"
                                        data-song-id="<?= $song['id'] ?>"
                                        onclick="event.stopPropagation(); toggleFavorite(this, <?= $song['id'] ?>)"
                                        title="Xóa khỏi danh sách yêu thích">
                                    <i class="fas fa-heart"></i>
                                </button>

                                <!-- DẤU + GÓC TRÁI DƯỚI -->
                                <button class="btn-add-to-album"
                                        data-song-id="<?= $song['id'] ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addToAlbumModal"
                                        onclick="event.stopPropagation();"
                                        title="Thêm vào album">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            <!-- Thông tin -->
                            <div class="p-4 text-center bg-gradient">
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
</div>

<?php 
include '../includes/player.php'; 
include '../includes/footer.php'; 
include '../includes/add_to_album_modal.php';
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<!-- CSS HOÀN HẢO – ĐỒNG BỘ 100% VỚI HOME, SEARCH, ALBUM -->
<style>
@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

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

.card-img-wrapper { 
    position: relative; 
    height: 260px; 
    overflow: hidden; 
}
.song-img { 
    width: 100%; height: 100%; 
    object-fit: cover; 
    transition: transform 0.8s ease; 
}

.play-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,0.95));
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.5s ease;
}
.play-overlay i { 
    color: white; 
    filter: drop-shadow(0 0 30px black); 
    transform: translateY(20px); 
    transition: all 0.5s; 
}

.song-card:hover .play-overlay { opacity: 1; }
.song-card:hover .play-overlay i { transform: translateY(0); }
.song-card:hover .song-img { transform: scale(1.25); }
.song-card:hover {
    transform: translateY(-20px) scale(1.05);
    border-color: #00D4FF;
    box-shadow: 0 40px 100px rgba(0,212,255,0.7);
}

/* TRÁI TIM ĐỎ – SIÊU ĐỈNH */
.btn-favorite {
    position: absolute; top: 12px; right: 12px;
    background: rgba(0,0,0,0.7);
    border: none; border-radius: 50%;
    width: 52px; height: 52px;
    color: white; font-size: 1.5rem;
    z-index: 10;
    transition: all 0.4s ease;
    display: flex; align-items: center; justify-content: center;
}
.btn-favorite.active {
    background: #e74c3c;
    transform: scale(1.2);
    box-shadow: 0 0 30px rgba(231,76,60,0.9);
}
.btn-favorite:hover {
    background: #c0392b;
    transform: scale(1.3);
}

/* DẤU + GÓC TRÁI DƯỚI */
.btn-add-to-album {
    position: absolute; bottom: 12px; left: 12px;
    background: rgba(0,0,0,0.8);
    border: none; border-radius: 50%;
    width: 56px; height: 56px;
    color: white; font-size: 1.9rem;
    z-index: 10;
    opacity: 0; transform: scale(0.8);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(12px);
}
.song-card:hover .btn-add-to-album {
    opacity: 1; transform: scale(1);
}
.btn-add-to-album:hover {
    background: #00D4FF;
    transform: scale(1.3);
    box-shadow: 0 0 50px rgba(0,212,255,0.9);
}

/* Responsive */
@media (max-width: 1200px) { .song-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); } }
@media (max-width: 992px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); } }
@media (max-width: 768px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1.5rem; } }
@media (max-width: 576px)  { 
    .song-grid { grid-template-columns: repeat(2, 1fr); gap: 1.2rem; padding: 1rem; }
}
</style>