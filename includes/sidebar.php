<?php 
// includes/sidebar.php - RESPONSIVE & AVATAR LINKED (FIXED LOGO)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}
$currentPage = basename($_SERVER['PHP_SELF']);

// Xử lý logic Avatar
$avatarName = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'avatar.jpg';
$avatarPath = "../assets/songs/images/" . $avatarName; 
?>

<button class="btn btn-dark d-lg-none position-fixed top-0 start-0 m-3 z-3 shadow-lg rounded-circle" 
        id="mobileSidebarToggle" 
        style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
    <i class="fas fa-bars fa-lg text-white"></i>
</button>

<div class="area">
    <ul class="circles">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
</div>

<div class="sidebar" id="sidebar">
    <button class="btn-close btn-close-white d-lg-none position-absolute top-0 end-0 m-3" id="sidebarClose"></button>

    <div class="logo-container text-center mt-2 mb-4">
        <a href="home.php">
            <img src="../assets/songs/images/logo.jpg" alt="Logo" class="sidebar-logo-img">
        </a>
    </div>

    <nav class="nav-menu">
        <a href="home.php" class="nav-link <?= $currentPage=='home.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i> <span>Trang chủ</span>
        </a>

        <a href="search.php" class="nav-link <?= $currentPage=='search.php' ? 'active' : '' ?>">
            <i class="fas fa-search"></i> <span>Tìm kiếm</span>
        </a>

        <a href="my_albums.php" class="nav-link <?= in_array($currentPage, ['my_albums.php', 'album_view.php', 'create_album.php', 'add_to_album.php']) ? 'active' : '' ?>">
            <i class="fas fa-compact-disc fa-spin-slow text-cyan"></i>
            <span class="fw-bold">Album của bạn</span>
        </a>

        <a href="charts.php" class="nav-link <?= $currentPage=='charts.php' ? 'active' : '' ?>">
            <i class="fas fa-trophy"></i> <span>Bảng xếp hạng</span>
        </a>

        <a href="favorites.php" class="nav-link <?= $currentPage=='favorites.php' ? 'active' : '' ?>">
            <i class="fas fa-heart text-danger"></i> <span>Yêu thích</span>
        </a>

        <a href="profile.php" class="nav-link <?= $currentPage=='profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i> <span>Hồ sơ cá nhân</span>
        </a>
    </nav>

    <?php if (isset($_SESSION['user_id']) && in_array($_SESSION['role'] ?? '', ['admin', 'staff'])): ?>
    <div class="admin-section mt-4 pt-4 border-top border-secondary border-opacity-25">
        <div class="admin-header d-flex align-items-center gap-2 mb-3 px-3">
            <i class="fas fa-crown text-warning"></i>
            <small class="text-warning fw-bold ls-1">QUẢN TRỊ</small>
        </div>
        <nav class="nav-admin">
            <a href="../admin/songs.php" class="nav-link admin <?= str_contains($_SERVER['REQUEST_URI'], '/admin/songs.php') ? 'active' : '' ?>">
                <i class="fas fa-music"></i> <span>QL Bài hát</span>
            </a>
            <a href="../admin/users.php" class="nav-link admin <?= str_contains($_SERVER['REQUEST_URI'], '/admin/users.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> <span>QL Người dùng</span>
            </a>
            <a href="../admin/statistics.php" class="nav-link admin <?= str_contains($_SERVER['REQUEST_URI'], '/admin/statistics.php') ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i> <span>Thống kê</span>
            </a>
        </nav>
    </div>
    <?php endif; ?>

    <div class="user-profile mt-auto">
        <hr class="sidebar-divider my-3 opacity-25">
        <div class="user-info p-3 rounded-3 bg-dark bg-opacity-25">
            <div class="d-flex align-items-center gap-3 mb-3">
                <a href="profile.php" class="flex-shrink-0">
                    <img src="<?= htmlspecialchars($avatarPath) ?>" 
                         alt="Avatar"
                         class="rounded-circle border border-secondary" 
                         style="width:45px; height:45px; object-fit:cover;"
                         onerror="this.src='../assets/songs/images/avatar.jpg'">
                </a>
                <div class="overflow-hidden">
                    <div class="fw-bold text-white text-truncate"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></div>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem;">
                        <?= ($_SESSION['role'] ?? 'user') === 'admin' ? 'Admin' : 'Member' ?>
                    </small>
                </div>
            </div>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm w-100 rounded-pill">
                <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</div>

<style>
/* --- CSS CHO LOGO MỚI --- */
.sidebar-logo-img {
    width: 140px;
    height: auto;
    object-fit: contain;
    transition: all 0.3s ease;
    /* Nếu ảnh nền đen muốn hòa trộn, bỏ comment dòng dưới */
    /* mix-blend-mode: screen; */
}
.sidebar-logo-img:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 0 10px rgba(0, 212, 255, 0.5));
}

/* Các CSS cũ giữ nguyên */
#mobileSidebarToggle { z-index: 1050; transition: transform 0.3s; }
#mobileSidebarToggle:active { transform: scale(0.9); }

.sidebar {
    width: 260px; height: 100vh; position: fixed; top: 0; left: 0;
    background: #0f0c29; z-index: 1040; overflow-y: auto;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex; flex-direction: column; padding: 20px;
    box-shadow: 4px 0 15px rgba(0,0,0,0.3);
}

@media (max-width: 991.98px) {
    .sidebar { transform: translateX(-100%); width: 280px; }
    body.sidebar-visible .sidebar { transform: translateX(0); }
    body.sidebar-visible::before {
        content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.7); z-index: 1030; backdrop-filter: blur(3px);
    }
    .main-content { margin-left: 0 !important; padding-top: 70px; }
}

@media (min-width: 992px) {
    .main-content { margin-left: 260px; }
}

.nav-link {
    color: #b3b3b3; padding: 12px 16px; border-radius: 8px; margin-bottom: 4px;
    display: flex; align-items: center; gap: 12px; transition: all 0.2s;
    text-decoration: none; font-weight: 500;
}
.nav-link:hover { color: #fff; background: rgba(255,255,255,0.1); transform: translateX(5px); }
.nav-link.active { background: linear-gradient(90deg, #00D4FF, #005bea); color: white; box-shadow: 0 4px 15px rgba(0,212,255,0.3); }
.nav-link i { width: 24px; text-align: center; }

.nav-link.admin { font-size: 0.9rem; padding: 10px 16px; }
.nav-link.admin.active { background: rgba(255, 193, 7, 0.2); color: #ffc107; border-left: 3px solid #ffc107; box-shadow: none; border-radius: 4px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('mobileSidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');
    const body = document.body;
    const sidebar = document.getElementById('sidebar');

    function closeSidebar() { body.classList.remove('sidebar-visible'); }

    if(toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            body.classList.toggle('sidebar-visible');
        });
    }

    if(closeBtn) { closeBtn.addEventListener('click', closeSidebar); }

    document.addEventListener('click', function(e) {
        if (body.classList.contains('sidebar-visible') && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSidebar();
    });
});
</script>