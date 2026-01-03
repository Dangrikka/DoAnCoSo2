<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require_once '../controllers/AlbumController.php';
require_once '../controllers/SongController.php';

$albumCtrl = new AlbumController();
$songCtrl  = new SongController();

$album_id = intval($_GET['id'] ?? 0);
if ($album_id <= 0) die("Album không tồn tại!");

$album = $albumCtrl->getAlbumById($album_id, $_SESSION['user_id']);
if (!$album) die("Album không thuộc về bạn!");

$songs = $songCtrl->getAllSongs();  // Lấy tất cả bài hát
$existingSongs = $albumCtrl->getSongsInAlbum($album_id);
$existingIds = array_column($existingSongs, 'id'); // mảng id bài có trong album

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content py-5">
    <div class="container-fluid px-4">

        <h1 class="display-4 fw-bold text-white mb-4">
            <i class="fas fa-music text-primary me-3"></i>
            Thêm bài hát vào album: <?= htmlspecialchars($album['name']) ?>
        </h1>

        <a href="album_view.php?id=<?= $album_id ?>" class="btn btn-secondary rounded-pill mb-4">
            <i class="fas fa-arrow-left"></i> Quay lại album
        </a>

        <div class="row g-4">
            <?php foreach ($songs as $song): 
                $audio = '../assets/songs/audio/' . $song['audio_file'];
                $image = '../assets/songs/images/' . $song['image'];
                $already = in_array($song['id'], $existingIds);
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="song-card p-3 rounded-4 bg-dark shadow-sm text-center">
                        <img src="<?= $image ?>" 
                             class="rounded mb-3" width="100%" height="180"
                             style="object-fit: cover;"
                             onerror="this.src='../assets/songs/images/default1.jpg'">
                        
                        <h5 class="text-white fw-bold mb-1 text-truncate">
                            <?= htmlspecialchars($song['title']) ?>
                        </h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($song['artist']) ?></p>

                        <?php if ($already): ?>
                            <button class="btn btn-secondary btn-sm rounded-pill" disabled>
                                <i class="fas fa-check"></i> Đã có trong album
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-sm rounded-pill"
                                    onclick="addSongToAlbum(<?= $song['id'] ?>, <?= $album_id ?>, this)">
                                <i class="fas fa-plus"></i> Thêm
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<?php include '../includes/footer.php'; ?>

<script>
function addSongToAlbum(songId, albumId, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('../ajax/add_to_album.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `song_id=${songId}&album_id=${albumId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fas fa-check"></i> Đã thêm';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus"></i> Thêm';
            alert(data.message || 'Lỗi');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i> Thêm';
        alert("Lỗi kết nối server!");
    });
}
</script>
