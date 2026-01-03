<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn phải đăng nhập.");
}

$song_id = isset($_GET['song_id']) ? (int)$_GET['song_id'] : 0;
if ($song_id <= 0) die("ID bài hát không hợp lệ.");

require_once '../controllers/AlbumController.php';

$albumCtrl = new AlbumController();

$albums = $albumCtrl->getUserAlbums($_SESSION['user_id']);


include '../includes/header.php';
include '../includes/sidebar.php';
?>
<link rel="stylesheet" href="../assets/css/style.css">
<div class="main-content">
<div class="container py-5">

<h2 class="text-gradient mb-4">Chọn Album để thêm bài hát</h2>

<?php if (empty($albums)): ?>
    <div class="alert alert-warning">Bạn chưa có album nào.</div>
<?php else: ?>
    <div class="row">

        <?php foreach ($albums as $album): ?>
        <div class="col-md-4 mb-4">
            <a href="add_song_to_album.php?album_id=<?= $album['id'] ?>&song_id=<?= $song_id ?>" 
               class="album-card select-album p-3 d-block text-center shadow rounded-3 text-decoration-none">

                <img src="../assets/albums/<?= $album['cover_image'] ?>" 
                     class="rounded-3 mb-3" 
                     style="width:160px;height:160px;object-fit:cover;">

                <h4 class="text-white"><?= htmlspecialchars($album['name']) ?></h4>
                <small class="text-muted">Ngày tạo: <?= date('d/m/Y', strtotime($album['created_at'])) ?></small>

            </a>
        </div>
        <?php endforeach; ?>

    </div>
<?php endif; ?>

</div>
</div>

<style>
.select-album:hover {
    transform: scale(1.05);
    transition: .2s;
    background: rgba(0,212,255,0.1);
}
</style>

<?php include '../includes/footer.php'; ?>
