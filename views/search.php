<?php
// views/search.php – SIÊU PHẨM TÌM KIẾM 2025 + LỊCH SỬ
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/SongController.php';

// --- XỬ LÝ LOGIC LỊCH SỬ TÌM KIẾM THEO TÀI KHOẢN ---

// 1. Tạo tên Cookie riêng biệt dựa trên user_id
$currentUserId = $_SESSION['user_id'];
$cookieName = 'history_user_' . $currentUserId;

// 2. Lấy lịch sử cũ của user này
$history = isset($_COOKIE[$cookieName]) ? json_decode($_COOKIE[$cookieName], true) : [];

// 3. Xử lý xóa lịch sử (chỉ xóa của user hiện tại)
if (isset($_GET['action']) && $_GET['action'] === 'clear_history') {
    setcookie($cookieName, '', time() - 3600, "/"); // Xóa cookie của user này
    header('Location: search.php');
    exit;
}

$searchQuery = trim($_GET['q'] ?? '');
$results = [];

if (!empty($searchQuery)) {
    // 4. Thêm từ khóa vào đầu mảng
    array_unshift($history, $searchQuery);
    
    // 5. Loại bỏ trùng lặp và giới hạn 10 từ khóa
    $history = array_slice(array_unique($history), 0, 10);
    
    // 6. Lưu lại vào Cookie riêng của user (thời hạn 30 ngày)
    setcookie($cookieName, json_encode($history), time() + (86400 * 30), "/");

    // 7. Tìm kiếm trong Database
    $songCtrl = new SongController();
    $results = $songCtrl->search($searchQuery, $currentUserId);
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container py-5">
        <h1 class="text-gradient display-3 fw-bold mb-5 text-center" 
            style="font-size: 5rem; text-shadow: 0 0 60px rgba(0,212,255,0.6);">
            Tìm Kiếm Nhạc
        </h1>

        <form method="get" class="mb-4" action="search.php">
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

        <?php if (empty($searchQuery)): ?>
            
            <?php if (!empty($history)): ?>
                <div class="history-section mx-auto" style="max-width: 800px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-muted text-uppercase fw-bold ls-1"><i class="fas fa-history me-2"></i>Tìm kiếm gần đây</h5>
                        <a href="?action=clear_history" class="btn-clear-history">
                            <i class="fas fa-trash-alt me-1"></i> Xóa lịch sử
                        </a>
                    </div>
                    <div class="history-tags">
                        <?php foreach ($history as $tag): ?>
                            <a href="?q=<?= urlencode($tag) ?>" class="history-chip">
                                <?= htmlspecialchars($tag) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            
            <div class="search-results fade-in-up">
                <h3 class="mb-5 text-center">
                    <span class="text-muted">Kết quả cho:</span>
                    <span class="text-primary display-6 fw-bold">"<?= htmlspecialchars($searchQuery) ?>"</span>
                    <span class="text-muted ms-3 fs-5">(<?= count($results) ?> kết quả)</span>
                </h3>

                <?php if (empty($results)): ?>
                    <div class="text-center py-5 my-5">
                        <i class="fas fa-search fa-6x text-primary mb-4 opacity-20"></i>
                        <h2 class="text-muted fw-light">Không tìm thấy bài hát nào</h2>
                        <p class="text-muted fs-4">Thử tìm với từ khóa khác nhé!</p>
                        <a href="search.php" class="btn btn-outline-light mt-3 rounded-pill px-4">Quay lại lịch sử</a>
                    </div>
                <?php else: ?>
                    <div class="song-grid">
                        <?php foreach ($results as $song): 
                            $isFavorite = !empty($song['is_favorite']);
                            $audioFile = '../assets/songs/audio/' . htmlspecialchars(basename($song['audio_file']));
                            $imageFile = !empty($song['image']) && $song['image'] !== 'default.jpg'
                                ? '../assets/songs/images/' . htmlspecialchars(basename($song['image']))
                                : '../assets/songs/images/default1.jpg';
                        ?>
                            <div class="song-item">
                                <div class="song-card"
                                     onclick="window.location.href='song_detail.php?id=<?= $song['id'] ?>'">

                                    <div class="card-img-wrapper position-relative overflow-hidden rounded-4 shadow-lg">
                                        <img src="<?= $imageFile ?>" class="song-img w-100 h-100" loading="lazy">
                                        
                                        <div class="play-overlay">
                                            <i class="fas fa-play-circle fa-5x"></i>
                                        </div>
                                        
                                        <button class="btn-favorite <?= $isFavorite ? 'active' : '' ?>"
                                                onclick="event.stopPropagation(); toggleFavorite(this, <?= $song['id'] ?>)"
                                                data-song-id="<?= $song['id'] ?>"
                                                title="<?= $isFavorite ? 'Bỏ thích' : 'Yêu thích' ?>">
                                            <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                                        </button>

                                        </div>

                                    <div class="p-4 text-center">
                                        <h6 class="fw-bold text-white mb-1 text-truncate"><?= htmlspecialchars($song['title']) ?></h6>
                                        <p class="small text-muted mb-0 text-truncate"><?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?></p>
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
// Đã xóa modal thêm album vì không dùng ở đây nữa
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<style>
/* --- 1. SEARCH FORM --- */
.search-form-wrapper { position: relative; max-width: 800px; margin: 0 auto; }
.search-input {
    width: 100%; padding: 1.5rem 6rem 1.5rem 3rem; font-size: 1.5rem;
    background: rgba(20,20,40,0.6); border: 2px solid rgba(255,255,255,0.1);
    border-radius: 60px; color: white; backdrop-filter: blur(15px);
    transition: all 0.4s ease;
}
.search-input:focus {
    outline: none; border-color: #00D4FF; background: rgba(0,0,0,0.8);
    box-shadow: 0 0 40px rgba(0,212,255,0.3);
}
.search-button {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    background: linear-gradient(135deg, #00D4FF, #8A2BE2);
    border: none; color: white; width: 65px; height: 65px;
    border-radius: 50%; font-size: 1.6rem; transition: 0.3s;
}
.search-button:hover { transform: translateY(-50%) scale(1.1); box-shadow: 0 0 30px rgba(0,212,255,0.6); }

/* --- 2. LỊCH SỬ TÌM KIẾM --- */
.history-section {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 20px;
    padding: 25px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.05);
    margin-top: 2rem;
    animation: fadeIn 0.8s ease;
}

.history-tags { display: flex; flex-wrap: wrap; gap: 12px; }

.history-chip {
    display: inline-block;
    padding: 10px 20px;
    background: rgba(255,255,255,0.08);
    color: #ccc;
    border-radius: 30px;
    text-decoration: none;
    font-size: 1rem;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.history-chip:hover {
    background: rgba(0, 212, 255, 0.2);
    color: #fff;
    border-color: #00D4FF;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.btn-clear-history {
    font-size: 0.9rem; color: #ff6b6b; text-decoration: none;
    padding: 5px 15px; border-radius: 20px; border: 1px solid rgba(255, 107, 107, 0.3);
    transition: 0.3s;
}
.btn-clear-history:hover { background: rgba(255, 107, 107, 0.1); color: #ff4757; }

/* --- 3. SONG GRID --- */
.song-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 2rem; padding: 2rem 1rem; justify-items: center;
}
.song-item { width: 100%; max-width: 260px; }
.song-card {
    background: rgba(20,20,40,0.95); border-radius: 20px;
    overflow: hidden; cursor: pointer; border: 2px solid transparent;
    transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative; backdrop-filter: blur(10px);
}
.card-img-wrapper { position: relative; height: 260px; overflow: hidden; }
.song-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }
.play-overlay {
    position: absolute; inset: 0; background: linear-gradient(180deg, transparent 40%, rgba(0,0,0,0.95));
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.5s ease;
}
.play-overlay i { color: white; filter: drop-shadow(0 0 30px black); transform: translateY(20px); transition: all 0.5s; }
.song-card:hover .play-overlay { opacity: 1; }
.song-card:hover .play-overlay i { transform: translateY(0); }
.song-card:hover .song-img { transform: scale(1.25); }
.song-card:hover { transform: translateY(-20px) scale(1.05); border-color: #00D4FF; box-shadow: 0 40px 100px rgba(0,212,255,0.5); }
.song-card.active { border-color: #00D4FF !important; box-shadow: 0 0 50px rgba(0,212,255,0.8) !important; }

/* Nút Tim */
.btn-favorite {
    position: absolute; border: none; border-radius: 50%;
    width: 50px; height: 50px; z-index: 10;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.4s ease;
    top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: white; font-size: 1.3rem;
}
.btn-favorite:hover, .btn-favorite.active { background: #e74c3c; transform: scale(1.1); }

/* Animation */
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.fade-in-up { animation: fadeIn 0.8s ease-out; }

/* Responsive */
@media (max-width: 992px)  { .song-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); } }
@media (max-width: 576px)  { 
    .song-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; padding: 0.5rem; }
    .search-input { font-size: 1.1rem; padding-right: 4rem; }
    .search-button { width: 50px; height: 50px; font-size: 1.2rem; }
}
</style>