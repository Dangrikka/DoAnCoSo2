<?php 
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
include 'includes/header.php';
include 'includes/sidebar.php';
require_once 'controllers/PlaylistController.php';
$playlistCtrl = new PlaylistController();
$playlists = $playlistCtrl->getUserPlaylists($_SESSION['user_id']);
?>
<div class="main-content">
  <h1 class="text-gradient display-4 fw-bold mb-4">Playlist của bạn</h1>
    <a href="add_playlist.php" class="btn btn-outline-primary mb-5 rounded-pill px-4" style="border-color:#00D4FF; color:#00D4FF;">
      <i class="fas fa-plus"></i> Tạo playlist mới
    </a>
  <div class="row g-4">
    <?php foreach($playlists as $pl): ?>
    <div class="col-md-4">
      <div class="song-card text-center p-4" style="height:280px;">
        <i class="fas fa-music fa-5x mb-3" style="color:#00D4FF;"></i>
  <h5><?php echo htmlspecialchars($pl['name']); ?></h5>
  <p class="text-muted"><?php echo isset($pl['songs_count']) ? $pl['songs_count'] : 0; ?> bài hát</p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include 'includes/player.php'; ?>
<?php include 'includes/footer.php'; ?>