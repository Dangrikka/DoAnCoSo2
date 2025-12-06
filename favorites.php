<?php

session_start();

// BẮT BUỘC ĐĂNG NHẬP
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// KẾT NỐI DATABASE
require_once '../config/database.php';

$user_id = (int)$_SESSION['user_id'];

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

// BẮT ĐẦU GIAO DIỆN
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4">
        <!-- TIÊU ĐỀ SIÊU ĐẸP -->
        <div class="text-center text-md-start mb-5">
            <h1 class="text-gradient display-2 fw-bold mb-3">
                <i class="fas fa-heart text-danger me-3"></i>
                Bài hát yêu thích
            </h1>
            <p class="text-muted fs-3 opacity-90">
                Bạn đang lưu <span class="text-primary fw-bold"><?= count($favorites) ?></span> 
                bản nhạc trong trái tim
            </p>
        </div>

        <!-- NẾU CHƯA CÓ BÀI NÀO -->
        <?php if (empty($favorites)): ?>
            <div class="text-center py-5 my-5">
                <i class="fas fa-heart-broken fa-9x text-muted mb-5 opacity-15"></i>
                <h2 class="text-muted fw-light mb-4">Chưa có bài hát nào được yêu thích</h2>
                <p class="text-muted fs-4 mb-5">Hãy khám phá và thêm những bản nhạc làm bạn rung động!</p>
                <a href="home.php" class="btn-primary px-6 py-3 rounded-pill fs-4 shadow-lg">
                    <i class="fas fa-home me-2"></i> Về trang chủ
                </a>
            </div>

        <!-- DANH SÁCH YÊU THÍCH -->
        <?php else: ?>
            <div class="song-grid">
                <?php foreach ($favorites as $song): 
                    // ĐƯỜNG DẪN CHUẨN TỪ ROOT WEB (QUAN TRỌNG NHẤT!)
                    $audioFile = 'assets/songs/audio/' . basename($song['audio_file']);
                    $imageFile = !empty($song['image']) && $song['image'] !== 'default.jpg'
                        ? 'assets/songs/images/' . basename($song['image'])
                        : 'assets/songs/images/default.jpg';
                ?>
                    <div class="song-item">
                        <div class="song-card"
                             data-song-id="<?= $song['id'] ?>"
                             data-title="<?= htmlspecialchars($song['title']) ?>"
                             data-artist="<?= htmlspecialchars($song['artist'] ?? 'Nghệ sĩ không rõ') ?>"
                             data-audio="<?= $audioFile ?>"
                             data-image="<?= $imageFile ?>"
                             data-is-favorite="true">

                            <!-- Ảnh bìa + hiệu ứng -->
                            <div class="position-relative overflow-hidden rounded-4 shadow-lg card-img-wrapper">
                                <img src="<?= $imageFile ?>"
                                     class="w-100 h-100 song-img"
                                     alt="<?= htmlspecialchars($song['title']) ?>"
                                     onerror="this.src='assets/songs/images/default.jpg'"
                                     loading="lazy">

                                <div class="play-overlay">
                                    <i class="fas fa-play-circle fa-5x"></i>
                                </div>

                                <!-- Nút yêu thích (luôn đỏ vì đang trong trang yêu thích) -->
                                <button class="btn-favorite active"
                                        data-song-id="<?= $song['id'] ?>"
                                        onclick="toggleFavorite(this, <?= $song['id'] ?>)"
                                        title="Xóa khỏi danh sách yêu thích">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>

                            <!-- Thông tin bài hát -->
                            <div class="p-3 text-center">
                                <h6 class="fw-bold text-white mb-1 text-truncate fs-6">
                                    <?= htmlspecialchars($song['title']) ?>
                                </h6>
                                <p class="small text-muted mb-0 text-truncate">
                                    <?= htmlspecialchars($song['artist'] ?? 'Nghệ sĩ không rõ') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CSS + Player + Footer + JS -->
<link rel="stylesheet" href="../assets/css/style.css">
<?php 
include '../includes/player.php'; 
include '../includes/footer.php'; 
?>

<!-- JS CHÍNH – BẮT BUỘC PHẢI CÓ -->
<script src="../assets/js/script.js"></script>