<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
$user_id = $_SESSION['user_id'];

// LẤY THÔNG TIN USER
$stmt = $GLOBALS['db_conn']->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ĐẾM THỐNG KÊ SIÊU ĐỈNH
$stats = [
    'favorites' => $GLOBALS['db_conn']->query("SELECT COUNT(*) FROM favorites WHERE user_id = $user_id")->fetch_row()[0],
    'albums'    => $GLOBALS['db_conn']->query("SELECT COUNT(*) FROM albums WHERE user_id = $user_id")->fetch_row()[0],
    'playtime'  => $GLOBALS['db_conn']->query("SELECT SUM(play_count) FROM play_counts pc JOIN favorites f ON pc.song_id = f.song_id WHERE f.user_id = $user_id")->fetch_row()[0] ?? 0
];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4">
        <!-- HEADER HỒ SƠ SIÊU ĐẸP -->
        <div class="profile-header text-center text-md-start mb-5">
            <div class="row align-items-center">
                <div class="col-lg-4 text-center mb-4 mb-lg-0">
                    <div class="avatar-wrapper position-relative d-inline-block">
                        <img src="../assets/songs/images/<?= $user['avatar'] ?? 'avatar.jpg' ?>" 
                             alt="Avatar" 
                             class="avatar-img rounded-circle shadow-lg"
                             onerror="this.src='../assets/songs/images/avatar.jpg'">
                        <div class="online-status"></div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold text-gradient mb-2"><?= htmlspecialchars($user['username']) ?></h1>
                    <p class="lead text-muted mb-4">
                        <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                    </p>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <h3 class="fw-bold text-primary"><?= number_format($stats['favorites']) ?></h3>
                            <small class="text-muted">Yêu thích</small>
                        </div>
                        <div class="stat-item">
                            <h3 class="fw-bold text-danger"><?= number_format($stats['albums']) ?></h3>
                            <small class="text-muted">Album</small>
                        </div>
                        <div class="stat-item">
                            <h3 class="fw-bold text-success"><?= number_format($stats['playtime']) ?></h3>
                            <small class="text-muted">Lượt phát</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NÚT CHỈNH SỬA HỒ SƠ -->
        <div class="text-center text-md-end mb-5">
            <button class="btn btn-outline-primary btn-lg px-5 rounded-pill shadow" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                <i class="fas fa-user-edit me-2"></i>Chỉnh sửa hồ sơ
            </button>
        </div>

        <!-- ALBUM CỦA BẠN -->
        <h2 class="text-gradient mb-4"><i class="fas fa-compact-disc me-3"></i>Album của bạn</h2>
        <?php
        $albums = $GLOBALS['db_conn']->query("SELECT * FROM albums WHERE user_id = $user_id ORDER BY created_at DESC");
        if ($albums->num_rows == 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-5x text-muted mb-4 opacity-50"></i>
                <p class="text-muted fs-3">Chưa có album nào</p>
                <a href="create_album.php" class="btn btn-primary px-5 py-3 rounded-pill">Tạo album đầu tiên</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php while($album = $albums->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3">
                        <a href="album_view.php?id=<?= $album['id'] ?>" class="text-decoration-none">
                            <div class="album-card">
                                <img src="../assets/songs/images/<?= $album['cover'] ?? 'default.jpg' ?>" 
                                     alt="" class="album-cover"
                                     onerror="this.src='../assets/songs/images/default.jpg'">
                                <div class="album-info">
                                    <h6 class="text-white fw-bold"><?= htmlspecialchars($album['name']) ?></h6>
                                    <small class="text-muted"><?= $album['created_at'] ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL CHỈNH SỬA HỒ SƠ -->
<div class="modal fade" id="editProfileModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h5 class="modal-title">Chỉnh sửa hồ sơ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="../ajax/update_profile.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img src="../assets/images/avatars/<?= $user['avatar'] ?? 'avatar.jpg' ?>" 
                             class="rounded-circle" width="120" id="previewAvatar">
                        <br><br>
                        <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewImg(this)">
                    </div>
                    <div class="mb-3">
                        <label>Tên hiển thị</label>
                        <input type="text" name="username" class="form-control bg-secondary text-white" 
                               value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control bg-secondary text-white" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary px-5">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-wrapper { position: relative; display: inline-block; }
.avatar-img { width: 220px; height: 220px; object-fit: cover; border: 6px solid rgba(0,212,255,0.4); transition: all 0.5s; }
.avatar-img:hover { transform: scale(1.05); border-color: #00D4FF; }
.online-status { 
    position: absolute; bottom: 20px; right: 20px; 
    width: 30px; height: 30px; background: #00ff00; 
    border: 5px solid #111; border-radius: 50%; box-shadow: 0 0 20px #00ff00; 
}
.stats-grid { display: flex; gap: 3rem; flex-wrap: wrap; }
.stat-item { text-align: center; }
.album-card { 
    background: rgba(20,20,40,0.9); border-radius: 16px; overflow: hidden; 
    transition: all 0.4s; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.album-card:hover { transform: translateY(-10px); box-shadow: 0 20px 50px rgba(0,212,255,0.4); }
.album-cover { width: 100%; height: 200px; object-fit: cover; }
.album-info { padding: 1rem; }
</style>

<script>
// Xem trước avatar
function previewImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = e => document.getElementById('previewAvatar').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<link rel="stylesheet" href="../assets/css/style.css">

<?php include '../includes/footer.php'; ?>