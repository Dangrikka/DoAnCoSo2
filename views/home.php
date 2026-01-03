<?php
// views/home.php – FINAL UPDATED (No Add Button, Link to Detail, Limit 5)
session_start();
require_once '../config/database.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../controllers/SongController.php';
$songCtrl = new SongController();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // YÊU CẦU: Giới hạn 5 bài hát mỗi trang

// Gọi controller (Lưu ý: Bạn cần đảm bảo hàm index trong SongController nhận tham số limit)
// Nếu hàm index của bạn chưa hỗ trợ tham số limit, hãy sửa trong Controller: index($userId, $page, $limit)
$data = $songCtrl->index($_SESSION['user_id']);

$songs = $data['songs'] ?? [];
$totalPages = $data['totalPages'] ?? 1;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">

    <div class="welcome-section mb-5 mt-4 text-center text-md-start px-4 px-lg-5 animate-fade-in">
        <h1 class="text-gradient display-3 fw-bold mb-2"
            style="font-size: 4rem; text-shadow: 0 0 50px rgba(0,212,255,0.4);">
            Xin chào, <span class="text-white"><?= htmlspecialchars($_SESSION['username']) ?>!</span>
        </h1>
        <p class="fs-4 text-white-50">
            Hôm nay bạn muốn nghe giai điệu gì?
        </p>
    </div>

    <?php if (empty($songs)): ?>
        <div class="text-center py-5 my-5">
            <div class="mb-4 position-relative d-inline-block">
                <div class="blob"></div>
                <i class="fas fa-music fa-7x text-secondary position-relative z-1 opacity-50"></i>
            </div>
            <h2 class="text-white fw-light mb-3">Chưa có bài hát nào</h2>
            <p class="text-muted fs-5">Hãy tải nhạc lên để bắt đầu hành trình âm nhạc!</p>
        </div>

    <?php else: ?>
        <div class="song-grid">

            <?php foreach ($songs as $song): 
                $isFavorite = !empty($song['is_favorite']);
                
                // Xử lý ảnh (ưu tiên ảnh upload -> ảnh default)
                $imageUrl = !empty($song['image_url']) 
                    ? '../assets/songs/images/' . basename($song['image_url'])
                    : (!empty($song['image']) 
                        ? '../assets/songs/images/' . basename($song['image'])
                        : '../assets/songs/images/default1.jpg');
            ?>
                <div class="song-item">
                    <div class="song-card"
                         onclick="window.location.href='song_detail.php?id=<?= $song['id'] ?>'">

                        <div class="card-img-wrapper">
                            <img src="<?= $imageUrl ?>" class="song-img"
                                 alt="<?= htmlspecialchars($song['title']) ?>"
                                 onerror="this.src='../assets/songs/images/default1.jpg'" loading="lazy">

                            <div class="play-overlay">
                                <i class="fas fa-play-circle fa-4x text-white"></i>
                            </div>

                            <button class="btn-favorite <?= $isFavorite ? 'active' : '' ?>"
                                    onclick="event.stopPropagation(); toggleFavorite(this, <?= $song['id'] ?>)"
                                    data-song-id="<?= $song['id'] ?>"
                                    title="<?= $isFavorite ? 'Bỏ thích' : 'Yêu thích' ?>">
                                <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                            </button>

                            </div>

                        <div class="p-3 text-center">
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

        <?php if ($totalPages > 1): ?>
        <div class="pagination d-flex justify-content-center my-5">
            <nav>
                <ul class="custom-pagination pagination">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                    </li>
                    <?php endif; ?>

                    <?php 
                    // Logic hiển thị phân trang
                    $range = 2;
                    for ($i = 1; $i <= $totalPages; $i++): 
                        if ($i == 1 || $i == $totalPages || ($i >= $page - $range && $i <= $page + $range)):
                    ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php elseif ($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; endfor; ?>

                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include '../includes/player.php'; ?>
<?php include '../includes/footer.php'; ?>

<script src="../assets/js/script.js"></script>

<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* --- 1. GRID LAYOUT --- */
.song-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 24px;
    padding-bottom: 50px;
    justify-content: center; 
}

/* --- 2. SONG CARD --- */
.song-card {
    background: rgba(24, 24, 24, 0.6);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: background-color 0.3s ease, transform 0.3s ease;
    border: 1px solid transparent;
    position: relative;
}

.song-card:hover {
    background: rgba(40, 40, 40, 1);
    transform: translateY(-8px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    border-color: rgba(255, 255, 255, 0.1);
}

/* Wrapper ảnh vuông 1:1 */
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

.song-card:hover .song-img {
    transform: scale(1.08);
}

/* --- 3. BUTTONS (CHỈ CÒN NÚT TIM) --- */
.btn-favorite {
    position: absolute;
    width: 36px; height: 36px;
    border-radius: 50%;
    border: none;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 10;
    opacity: 0;
    /* Góc TRÊN Phải */
    top: 10px; right: 10px; 
    background: rgba(0,0,0,0.6); 
    color: #ccc; 
    font-size: 1.1rem;
    transform: translateY(-10px);
}
.btn-favorite.active { color: #e74c3c; opacity: 1; transform: translateY(0); } /* Luôn hiện nếu đã like */

/* Hover Effects */
.song-card:hover .btn-favorite { 
    opacity: 1; transform: translateY(0); 
}
.btn-favorite:hover { background: #e74c3c; color: white; transform: scale(1.15) !important; }

/* Play Overlay Center */
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

/* --- 4. TEXT & PAGINATION --- */
.text-gradient {
    background: linear-gradient(135deg, #00D4FF, #9D4EDD);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.blob {
    position: absolute; top: 50%; left: 50%;
    width: 150px; height: 150px;
    background: radial-gradient(circle, rgba(0,212,255,0.3) 0%, rgba(0,0,0,0) 70%);
    transform: translate(-50%, -50%);
    animation: pulse 3s infinite;
}

.custom-pagination .page-link {
    background: transparent; border: none; color: #a0a0a0;
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 4px; transition: 0.3s;
}
.custom-pagination .page-link:hover { background: rgba(255,255,255,0.1); color: white; }
.custom-pagination .active .page-link { background: #00D4FF; color: black; font-weight: bold; box-shadow: 0 0 15px rgba(0,212,255,0.5); }
.custom-pagination .disabled .page-link { color: #555; cursor: default; }

@media (max-width: 576px) {
    .song-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .display-3 { font-size: 2.5rem !important; }
}
</style>