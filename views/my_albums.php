<?php
// my_albums.php – ALBUM CỦA BẠN – ĐẸP NHƯ SPOTIFY THẬT 2025

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../controllers/AlbumController.php';

$albumCtrl = new AlbumController();
$albums = $albumCtrl->getUserAlbums($_SESSION['user_id']);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4">

        <!-- HEADER + NÚT TẠO ALBUM -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="display-3 fw-bold text-gradient">
                    <i class="fas fa-compact-disc fa-spin-slow text-primary me-3"></i>
                    Album của bạn
                </h1>
                <p class="text-muted fs-5">Tổng cộng: <strong><?= count($albums) ?></strong> album</p>
            </div>
            <a href="create_album.php" class="btn btn-primary btn-lg rounded-pill shadow-lg px-5 py-3">
                <i class="fas fa-plus me-2"></i> Tạo album mới
            </a>
        </div>

        <!-- DANH SÁCH ALBUM -->
        <?php if (empty($albums)): ?>
            <div class="text-center py-5 my-5">
                <i class="fas fa-compact-disc fa-8x text-muted mb-4 opacity-30"></i>
                <h2 class="text-muted fw-light">Bạn chưa có album nào</h2>
                <p class="text-muted fs-4 mb-4">Hãy tạo album đầu tiên để lưu giữ những bài hát yêu thích!</p>
                <a href="create_album.php" class="btn btn-primary btn-lg rounded-pill px-6 py-3 shadow-lg">
                    <i class="fas fa-plus me-2"></i> Tạo album đầu tiên
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($albums as $album): 
                    $songCount = $albumCtrl->countSongs($album['id']);
                    $coverImg = !empty($album['cover_image']) 
                        ? '../assets/songs/images/' . basename($album['cover_image'])
                        : '../assets/songs/images/default.jpg';
                ?>
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="album-card rounded-4 overflow-hidden shadow-lg transition-all"
                             onclick="location.href='album_view.php?id=<?= $album['id'] ?>'"
                             style="cursor:pointer;">
                            
                            <div class="position-relative">
                                <img src="<?= $coverImg ?>" 
                                     class="w-100" 
                                     style="height:240px;object-fit:cover;"
                                     onerror="this.src='../assets/songs/images/default.jpg'">
                                <div class="play-overlay">
                                    <i class="fas fa-play fa-3x"></i>
                                </div>
                            </div>

                            <div class="p-3 bg-dark bg-gradient">
                                <h6 class="text-white fw-bold text-truncate mb-1">
                                    <?= htmlspecialchars($album['name']) ?>
                                </h6>
                                <p class="text-muted small mb-0">
                                    <?= $songCount ?> bài hát • <?= date('Y', strtotime($album['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/player.php'; include '../includes/footer.php'; ?>

<link rel="stylesheet" href="../assets/css/style.css">
 <script src="../assets/js/script.js"></script>
<style>
.album-card {
    transition: all 0.4s ease;
    border: 2px solid transparent;
}

.album-card:hover {
    transform: translateY(-15px) scale(1.03);
    box-shadow: 0 25px 60px rgba(0, 212, 255, 0.5) !important;
    border-color: #00D4FF;
}

.play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, 20px);
    background: #00D4FF;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.4s ease;
    box-shadow: 0 15px 40px rgba(0,212,255,0.6);
}

.album-card:hover .play-overlay {
    opacity: 1;
    transform: translate(-50%, -50%);
}

.fa-spin-slow {
    animation: spin 6s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.text-gradient {
    background: linear-gradient(90deg, #00D4FF, #9D4EDD);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>