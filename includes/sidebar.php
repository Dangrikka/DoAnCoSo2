<?php 
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Particle Background -->
<div class="area">
    <ul class="circles">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
</div>

<div class="sidebar">
    <!-- Logo -->
    <div class="logo">
        <span class="text-nhac">NHẠC</span>
        <span class="text-hay">HAY</span>
    </div>

    <!-- Menu người dùng -->
    <nav class="nav-menu">
        <a href="home.php" class="nav-link <?= $currentPage=='home.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Trang chủ</span>
        </a>

        <a href="search.php" class="nav-link <?= $currentPage=='search.php' ? 'active' : '' ?>">
            <i class="fas fa-search"></i>
            <span>Tìm kiếm</span>
        </a>

        <!-- ALBUM CỦA BẠN – ĐẸP NHƯ SPOTIFY THẬT -->
        <a href="my_albums.php" class="nav-link <?= in_array($currentPage, ['my_albums.php', 'album_view.php', 'create_album.php', 'add_to_album.php']) ? 'active' : '' ?>">
            <i class="fas fa-compact-disc fa-spin-slow text-cyan"></i>
            <span class="fw-bold">Album của bạn</span>
        </a>

        <a href="charts.php" class="nav-link <?= $currentPage=='charts.php' ? 'active' : '' ?>">
            <i class="fas fa-trophy"></i>
            <span>Bảng xếp hạng</span>
        </a>

        <a href="favorites.php" class="nav-link <?= $currentPage=='favorites.php' ? 'active' : '' ?>">
            <i class="fas fa-heart text-danger"></i>
            <span>Yêu thích</span>
        </a>

        <a href="profile.php" class="nav-link <?= $currentPage=='profile.php' ? 'active' : '' ?>">
            <i class="fas fa-user"></i>
            <span>Hồ sơ cá nhân</span>
        </a>

    </nav>

    <!-- PHẦN ADMIN -->
    <?php if ($_SESSION['role'] ?? '' === 'admin'): ?>
        <div class="admin-section mt-5 pt-4 border-top border-secondary">
            <div class="admin-header d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-crown text-warning fa-lg"></i>
                <span class="text-warning fw-bold">QUẢN TRỊ VIÊN</span>
            </div>
            <nav class="nav-admin">
                <a href="../admin/songs.php" class="nav-link admin <?= strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'active' : '' ?>">
                    <i class="fas fa-music"></i> Quản lý bài hát
                </a>
                <a href="../admin/users.php" class="nav-link admin">
                    <i class="fas fa-users"></i> Quản lý người dùng
                </a>
                <a href="../admin/statistics.php" class="nav-link admin">
                    <i class="fas fa-chart-bar"></i> Thống kê
                </a>
                <a href="../admin/settings.php" class="nav-link admin">
                    <i class="fas fa-cog"></i> Cài đặt
                </a>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Thông tin người dùng -->
    <div class="user-profile mt-auto">
        <hr class="sidebar-divider">
        <div class="user-info p-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <?php if (!empty($_SESSION['avatar'])): ?>
                    <img src="<?= htmlspecialchars($_SESSION['avatar']) ?>" class="rounded-circle shadow" style="width:50px;height:50px;object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-3x text-primary"></i>
                <?php endif; ?>
                <div>
                    <div class="fw-bold text-white text-truncate" style="max-width:140px;">
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </div>
                    <small class="text-muted">
                        <?= ($_SESSION['role'] ?? 'user') === 'admin' ? 'Quản trị viên' : 'Thành viên' ?>
                    </small>
                </div>
            </div>
            <a href="logout.php" class="btn-logout w-100 text-start mt-2">
                <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
            </a>
        </div>
    </div>
</div>

