<?php
// views/profile.php – NO MUSIC ICON
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

// ĐẾM THỐNG KÊ
$stats = [
    'favorites' => $GLOBALS['db_conn']->query("SELECT COUNT(*) FROM favorites WHERE user_id = $user_id")->fetch_row()[0],
    'albums'    => $GLOBALS['db_conn']->query("SELECT COUNT(*) FROM albums WHERE user_id = $user_id")->fetch_row()[0],
    'playtime'  => $GLOBALS['db_conn']->query("SELECT SUM(play_count) FROM play_counts pc JOIN favorites f ON pc.song_id = f.song_id WHERE f.user_id = $user_id")->fetch_row()[0] ?? 0
];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4 px-lg-5">
        
        <div class="profile-header position-relative overflow-hidden rounded-4 p-5 mb-5 animate-fade-in shadow-lg"
             style="background: linear-gradient(135deg, rgba(20,20,40,0.8), rgba(40,40,60,0.9)); backdrop-filter: blur(20px);">
            
            <div class="row align-items-center position-relative z-1">
                <div class="col-lg-3 text-center mb-4 mb-lg-0">
                    <div class="avatar-wrapper position-relative d-inline-block">
                        <img src="../assets/songs/images/<?= !empty($user['avatar']) ? $user['avatar'] : 'avatar.jpg' ?>" 
                             alt="Avatar" 
                             class="avatar-img rounded-circle shadow-lg"
                             onerror="this.src='../assets/songs/images/avatar.jpg'">
                        <div class="online-status" title="Đang hoạt động"></div>
                        
                        <a href="edit_profile.php" class="btn-change-avatar d-flex align-items-center justify-content-center text-decoration-none">
                            <i class="fas fa-camera"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="text-uppercase text-muted fw-bold ls-2 mb-1">Hồ sơ cá nhân</h6>
                            <h1 class="display-4 fw-bold text-white mb-0"><?= htmlspecialchars($user['username']) ?></h1>
                            <p class="text-white-50 mt-1"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        
                        <a href="edit_profile.php" class="btn btn-outline-light rounded-pill px-4 py-2 mt-3 mt-md-0 fw-bold hover-scale text-decoration-none">
                            <i class="fas fa-pen me-2"></i> Chỉnh sửa
                        </a>
                    </div>

                    <div class="stats-grid d-flex gap-4 mt-4 text-center text-md-start justify-content-center justify-content-md-start">
                        <div class="stat-card p-3 rounded-3 bg-dark bg-opacity-50">
                            <h3 class="fw-bold text-primary mb-0"><?= number_format($stats['favorites']) ?></h3>
                            <small class="text-muted text-uppercase fw-bold">Yêu thích</small>
                        </div>
                        <div class="stat-card p-3 rounded-3 bg-dark bg-opacity-50">
                            <h3 class="fw-bold text-danger mb-0"><?= number_format($stats['albums']) ?></h3>
                            <small class="text-muted text-uppercase fw-bold">Album</small>
                        </div>
                        <div class="stat-card p-3 rounded-3 bg-dark bg-opacity-50">
                            <h3 class="fw-bold text-success mb-0"><?= number_format($stats['playtime']) ?></h3>
                            <small class="text-muted text-uppercase fw-bold">Lượt nghe</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav nav-pills mb-4 gap-3" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 fw-bold" id="albums-tab" data-bs-toggle="tab" data-bs-target="#albums" type="button" role="tab">
                    <i class="fas fa-compact-disc me-2"></i>Album của bạn
                </button>
            </li>
        </ul>

        <div class="tab-content" id="profileTabsContent">
            <div class="tab-pane fade show active" id="albums" role="tabpanel">
                <?php
                $albums = $GLOBALS['db_conn']->query("SELECT * FROM albums WHERE user_id = $user_id ORDER BY created_at DESC");
                
                if ($albums->num_rows == 0): ?>
                    <div class="empty-state text-center py-5">
                        <div class="mb-3 position-relative d-inline-block">
                             <div class="blob-folder"></div>
                             <i class="fas fa-folder-open fa-6x text-secondary position-relative z-1 opacity-50"></i>
                        </div>
                        <h3 class="text-white fw-light mb-3">Chưa có album nào</h3>
                        <p class="text-muted mb-4">Tạo album để lưu giữ những bài hát theo tâm trạng của bạn.</p>
                        <a href="create_album.php" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow hover-scale">
                            <i class="fas fa-plus me-2"></i>Tạo Album
                        </a>
                    </div>
                <?php else: ?>
                    <div class="album-grid">
                        <?php while($album = $albums->fetch_assoc()): 
                             $cover = !empty($album['cover_image']) && file_exists('../assets/albums/' . basename($album['cover_image']))
                                ? '../assets/albums/' . htmlspecialchars(basename($album['cover_image']))
                                : '../assets/songs/images/default1.jpg';
                        ?>
                            <div class="album-card-item" onclick="location.href='album_view.php?id=<?= $album['id'] ?>'">
                                <div class="album-img-wrapper">
                                    <img src="<?= $cover ?>" alt="<?= htmlspecialchars($album['name']) ?>" loading="lazy">
                                    <div class="album-overlay">
                                        <i class="fas fa-play-circle fa-3x text-white"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6 class="text-white fw-bold mb-1 text-truncate"><?= htmlspecialchars($album['name']) ?></h6>
                                    <small class="text-muted"><?= date('Y', strtotime($album['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<style>
/* Header Styles */
.avatar-wrapper { position: relative; }
.avatar-img { 
    width: 200px; height: 200px; 
    object-fit: cover; 
    border: 4px solid rgba(255,255,255,0.1); 
    transition: transform 0.5s ease; 
}
.avatar-wrapper:hover .avatar-img { transform: scale(1.05); border-color: #00D4FF; }

.online-status { 
    position: absolute; bottom: 15px; right: 15px; 
    width: 24px; height: 24px; 
    background: #2ecc71; 
    border: 4px solid #222; 
    border-radius: 50%; 
    box-shadow: 0 0 15px #2ecc71; 
}

.btn-change-avatar {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.8);
    background: rgba(0,0,0,0.6); color: white; border: none; border-radius: 50%;
    width: 50px; height: 50px; font-size: 1.2rem;
    opacity: 0; transition: all 0.3s;
}
.avatar-wrapper:hover .btn-change-avatar { opacity: 1; transform: translate(-50%, -50%) scale(1); }

/* Grid Styles */
.album-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 24px;
}
.album-card-item { cursor: pointer; transition: transform 0.3s; }
.album-card-item:hover { transform: translateY(-8px); }

.album-img-wrapper {
    position: relative; width: 100%; aspect-ratio: 1/1;
    border-radius: 12px; overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}
.album-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.album-card-item:hover img { transform: scale(1.1); }

.album-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,0.4);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: 0.3s; backdrop-filter: blur(2px);
}
.album-card-item:hover .album-overlay { opacity: 1; }

.blob-folder {
    position: absolute; top: 50%; left: 50%;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(108,117,125,0.3) 0%, transparent 70%);
    transform: translate(-50%, -50%); animation: pulse 3s infinite;
}

@media (max-width: 768px) {
    .avatar-img { width: 150px; height: 150px; }
    .display-4 { font-size: 2.5rem; }
    .album-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
}
</style>

<?php include '../includes/footer.php'; ?>