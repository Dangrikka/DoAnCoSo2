<?php
// my_albums.php – ALBUM CỦA BẠN (Nút Play chính giữa)

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
    <div class="container-fluid px-4 px-lg-5">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-end mb-5 gap-3">
            <div>
                <h6 class="text-uppercase text-muted fw-bold ls-2 mb-2">Thư viện</h6>
                <h1 class="display-4 fw-bold text-white mb-0" style="text-shadow: 0 0 30px rgba(255,255,255,0.1);">
                    Album của bạn
                </h1>
                <p class="text-muted mt-2 mb-0"><i class="fas fa-compact-disc me-2"></i><?= count($albums) ?> bộ sưu tập</p>
            </div>
            
            <a href="create_album.php" class="btn-create-album">
                <span class="icon-box"><i class="fas fa-plus"></i></span>
                <span class="text">Tạo Album Mới</span>
            </a>
        </div>

        <?php if (empty($albums)): ?>
            <div class="empty-state-box text-center py-5">
                <div class="mb-4 position-relative d-inline-block">
                    <div class="blob"></div>
                    <i class="fas fa-record-vinyl fa-6x text-white position-relative z-1"></i>
                </div>
                <h2 class="text-white fw-bold mb-3">Chưa có album nào</h2>
                <p class="text-muted fs-5 mb-4 mw-600 mx-auto">
                    Tạo album để lưu giữ những bài hát theo tâm trạng của riêng bạn.
                </p>
                <a href="create_album.php" class="btn btn-primary rounded-pill px-5 py-3 fw-bold shadow-lg hover-scale">
                    Bắt đầu tạo ngay
                </a>
            </div>
        <?php else: ?>
            <div class="album-grid">
                <?php foreach ($albums as $album): 
                    $songCount = $albumCtrl->countSongs($album['id']);
                    $coverImg = !empty($album['cover_image']) && file_exists('../assets/albums/' . basename($album['cover_image']))
                        ? '../assets/albums/' . basename($album['cover_image'])
                        : '../assets/songs/images/default1.jpg';
                ?>
                    <div class="album-card" onclick="location.href='album_view.php?id=<?= $album['id'] ?>'">
                        <div class="album-img-wrapper">
                            <img src="<?= $coverImg ?>" 
                                 alt="<?= htmlspecialchars($album['name']) ?>" 
                                 loading="lazy"
                                 onerror="this.src='../assets/songs/images/default1.jpg'">
                            
                            <div class="play-overlay">
                                <i class="fas fa-play-circle"></i>
                            </div>
                        </div>

                        <div class="album-info">
                            <h5 class="album-title text-truncate"><?= htmlspecialchars($album['name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="album-meta text-truncate">
                                    <?= date('Y', strtotime($album['created_at'])) ?> • <?= $songCount ?> bài
                                </span>
                                
                                <div class="dropdown" onclick="event.stopPropagation();">
                                    <button class="btn btn-link text-muted p-0" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark shadow">
                                        <li><a class="dropdown-item" href="edit_album.php?id=<?= $album['id'] ?>"><i class="fas fa-pen me-2"></i>Sửa</a></li>
                                        <li><a class="dropdown-item text-danger" href="delete_album.php?id=<?= $album['id'] ?>" onclick="return confirm('Xóa album này?')"><i class="fas fa-trash me-2"></i>Xóa</a></li>
                                    </ul>
                                </div>
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
/* --- 1. LAYOUT GRID --- */
.album-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 24px;
    padding-bottom: 50px;
}

/* --- 2. ALBUM CARD STYLE --- */
.album-card {
    background: rgba(24, 24, 24, 0.6);
    padding: 16px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
    cursor: pointer;
    border: 1px solid transparent;
}

.album-card:hover {
    background: rgba(40, 40, 40, 1);
    border-color: rgba(255,255,255,0.05);
}

/* Wrapper ảnh */
.album-img-wrapper {
    position: relative;
    width: 100%;
    aspect-ratio: 1 / 1;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
    transition: box-shadow 0.3s ease;
}

.album-card:hover .album-img-wrapper {
    box-shadow: 0 12px 30px rgba(0,0,0,0.7);
}

.album-img-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

/* Hiệu ứng Zoom ảnh nhẹ khi hover */
.album-card:hover .album-img-wrapper img {
    transform: scale(1.05);
}

/* --- 3. PLAY OVERLAY (SỬA LẠI: CENTER) --- */
.play-overlay {
    position: absolute;
    inset: 0; /* Full cover ảnh */
    background: rgba(0,0,0,0.5); /* Nền tối mờ */
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    backdrop-filter: blur(2px); /* Làm mờ ảnh nền một chút */
}

.play-overlay i {
    font-size: 4rem; /* Icon to */
    color: white;
    filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));
    transform: scale(0.8);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Hiệu ứng khi Hover vào Card */
.album-card:hover .play-overlay {
    opacity: 1;
}

.album-card:hover .play-overlay i {
    transform: scale(1); /* Phóng to icon lên */
}

/* --- 4. TYPOGRAPHY --- */
.album-title {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 4px;
}

.album-meta {
    color: #b3b3b3;
    font-size: 0.85rem;
    font-weight: 500;
}

/* --- 5. BUTTON TẠO ALBUM --- */
.btn-create-album {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,0.1);
    padding: 10px 24px 10px 10px;
    border-radius: 50px;
    text-decoration: none;
    color: white;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.1);
    transition: 0.3s;
}

.btn-create-album .icon-box {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, #00D4FF, #9D4EDD);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-right: 12px;
}

.btn-create-album:hover {
    background: rgba(255,255,255,0.2);
    color: white;
    transform: translateY(-2px);
}

/* --- 6. EMPTY STATE BLOB --- */
.blob {
    position: absolute;
    top: 50%; left: 50%;
    width: 120px; height: 120px;
    background: linear-gradient(135deg, #00D4FF, #9D4EDD);
    border-radius: 50%;
    filter: blur(40px);
    opacity: 0.5;
    transform: translate(-50%, -50%);
    animation: pulse 3s infinite;
}

@keyframes pulse {
    0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.5; }
    50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.7; }
    100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.5; }
}

/* --- RESPONSIVE --- */
@media (max-width: 768px) {
    .album-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .btn-create-album { width: 100%; justify-content: center; }
}
@media (max-width: 480px) {
    .album-card { padding: 12px; }
}
</style>