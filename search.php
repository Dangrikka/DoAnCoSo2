<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php'; // Đường dẫn đúng tùy cấu trúc thư mục
$keyword = trim($_GET['q'] ?? '');

// TRUY VẤN TRỰC TIẾP – NHANH, ỔN ĐỊNH, KHÔNG LỖI
$sql = "SELECT s.*, 
        (SELECT COUNT(*) FROM favorites f WHERE f.song_id = s.id AND f.user_id = ?) as is_favorite
        FROM songs s 
        WHERE s.status = 'active'";

$params = [$_SESSION['user_id']];
$types = "i";

if ($keyword !== '') {
    $sql .= " AND (s.title LIKE ? OR s.artist LIKE ?)";
    $searchTerm = "%$keyword%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

$sql .= " ORDER BY s.play_count DESC, s.created_at DESC LIMIT 100";

$stmt = $db_conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$songs = $result->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4">
        <h1 class="text-gradient display-4 fw-bold mb-4 text-center">
            <i class="fas fa-search me-3"></i>
            Tìm kiếm nhạc
        </h1>

        <!-- FORM TÌM KIẾM SIÊU ĐẸP -->
        <form method="GET" class="mb-5 max-w-600 mx-auto">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-transparent border-0 text-primary">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    type="text" 
                    name="q" 
                    value="<?= htmlspecialchars($keyword) ?>" 
                    class="form-control rounded-pill shadow-lg" 
                    placeholder="Nhập tên bài hát, ca sĩ..." 
                    autofocus
                    style="background:rgba(255,255,255,0.08); border:2px solid #00D4FF; color:white; backdrop-filter:blur(10px);">
                <button type="submit" class="btn btn-primary rounded-pill px-5">
                    Tìm ngay
                </button>
            </div>
        </form>

        <!-- KẾT QUẢ TÌM KIẾM -->
        <?php if (empty($songs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-music fa-5x text-muted mb-4 opacity-50"></i>
                <h3 class="text-muted">Không tìm thấy bài hát nào</h3>
                <p>Hãy thử tìm với từ khóa khác nhé!</p>
            </div>
        <?php else: ?>
            <div class="song-grid">
                <?php foreach($songs as $song): ?>
                    <div class="song-card position-relative overflow-hidden rounded-4 shadow-lg"
                         data-song-id="<?= $song['id'] ?>"
                         data-title="<?= htmlspecialchars($song['title']) ?>"
                         data-artist="<?= htmlspecialchars($song['artist']) ?>"
                         data-audio="<?= htmlspecialchars($song['audio_url']) ?>"
                         data-image="<?= htmlspecialchars($song['image_url']) ?>"
                         data-is-favorite="<?= $song['is_favorite'] ?>">

                        <img src="<?= htmlspecialchars($song['image_url']) ?>" 
                             alt="<?= htmlspecialchars($song['title']) ?>"
                             class="song-img w-100 h-100 object-fit-cover">

                        <div class="play-overlay">
                            <i class="fas fa-play-circle fa-4x"></i>
                        </div>

                        <!-- NÚT YÊU THÍCH -->
                        <button class="btn-favorite position-absolute top-0 end-0 m-3" 
                                data-song-id="<?= $song['id'] ?>"
                                title="Yêu thích">
                            <i class="<?= $song['is_favorite'] ? 'fas fa-heart text-danger' : 'far fa-heart' ?> fa-lg"></i>
                        </button>

                        <div class="p-3 text-center">
                            <h6 class="text-white mb-1 text-truncate"><?= htmlspecialchars($song['title']) ?></h6>
                            <p class="small text-muted mb-0 text-truncate"><?= htmlspecialchars($song['artist']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/player.php'; ?>
<?php include '../includes/footer.php'; ?>