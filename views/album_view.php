<?php
// views/album_view.php – SIÊU PHẨM ALBUM 2025 – THÊM/XÓA KHÔNG RELOAD, MƯỢT NHƯ SPOTIFY THỤY ĐIỂN!
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AlbumController.php';

$albumCtrl = new AlbumController();
$album_id = intval($_GET['id'] ?? 0);

if ($album_id <= 0) {
    die('<div class="text-center py-5"><h1 class="text-danger">Album không tồn tại!</h1><a href="../home.php" class="btn btn-primary mt-4">Về trang chủ</a></div>');
}

$album = $albumCtrl->getAlbumById($album_id, $_SESSION['user_id']);
if (!$album) {
    die('<div class="text-center py-5"><h1 class="text-muted">Không tìm thấy album!</h1><a href="../home.php" class="btn btn-primary mt-4 px-5 py-3 rounded-pill">Về trang chủ</a></div>');
}

$songs = $albumCtrl->getSongsInAlbum($album_id);
$coverImg = !empty($album['cover_image']) 
    ? '../assets/albums/' . basename($album['cover_image']) 
    : '../assets/songs/images/default.jpg';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?> 

<div class="main-content py-5">
    <div class="container-fluid px-4">
        <!-- HEADER ALBUM SIÊU ĐỈNH -->
        <div class="row align-items-end g-5 mb-5">
            <div class="col-12 col-md-4 col-lg-3 text-center text-md-start">
                <div class="shadow-2xl rounded-4 overflow-hidden">
                    <img src="<?= $coverImg ?>" 
                         class="img-fluid" 
                         style="width:100%;max-width:350px;height:auto;aspect-ratio:1;object-fit:cover;"
                         onerror="this.src='../assets/songs/images/default.jpg'"
                         alt="Album Cover">
                </div>
            </div>
            <div class="col-12 col-md-8 col-lg-9">
                <p class="text-primary text-uppercase fw-bold mb-2 fs-5">ALBUM</p>
                <h1 class="display-1 fw-bold text-gradient mb-3" style="font-size:5rem;line-height:1;">
                    <?= htmlspecialchars($album['name']) ?>
                </h1>
                <div class="d-flex align-items-center gap-4 flex-wrap">
                    <p class="text-white fs-4 mb-0">
                        <i class="fas fa-user me-2"></i>
                        <strong><?= htmlspecialchars($_SESSION['username'] ?? 'Bạn') ?></strong>
                    </p>
                    <p class="text-muted fs-4 mb-0">
                        • <span id="songCount"><?= count($songs) ?></span> bài hát
                    </p>
                    <p class="text-muted fs-4 mb-0">
                        • <?= date('Y', strtotime($album['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>

<!-- NÚT PLAY + THÊM BÀI -->
<div class="mb-5 d-flex flex-wrap gap-4 align-items-center">
    <!-- gọi playAlbum với album id (hàm sẽ nhận tham số) -->
    <button class="btn-play-big shadow-lg" onclick="playAlbum(<?= $album_id ?>)">
        <i class="fas fa-play"></i>
    </button>

    <!-- truyền album-id vào button để modal biết -->
    <a href="album_add_songs.php?id=<?= $album_id ?>" 
   class="btn btn-outline-primary px-5 py-3 rounded-pill fs-5 d-flex align-items-center gap-2">
    <i class="fas fa-plus"></i> Thêm bài hát
    </a>


    <div class="ms-auto">
        <small class="text-muted">Tạo ngày <?= isset($album['created_at']) ? date('d/m/Y', strtotime($album['created_at'])) : '—' ?></small>
    </div>
</div>


        <!-- DANH SÁCH BÀI HÁT -->
        <div id="albumSongsContainer">
            <?php if (!empty($songs)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle bg-dark bg-opacity-95 rounded-4 overflow-hidden">
                        <thead class="text-muted small text-uppercase border-bottom border-secondary">
                            <tr>
                                <th class="text-center" width="60">#</th>
                                <th>TIÊU ĐỀ</th>
                                <th width="200">NGHỆ SĨ</th>
                                <th width="100" class="text-center">XÓA</th>
                            </tr>
                        </thead>
                        <tbody class="text-white" id="albumSongsList">
                            <?php foreach ($songs as $i => $song): 
                                $audio = '../assets/songs/audio/' . htmlspecialchars($song['audio_file']);
                                $img   = !empty($song['image']) 
                                    ? '../assets/songs/images/' . basename($song['image']) 
                                    : '../assets/songs/images/default1.jpg';
                            ?>
                                <tr class="song-row align-middle border-bottom border-dark" 
                                    style="cursor:pointer;transition:all 0.3s;"
                                    data-song-id="<?= $song['id'] ?>"
                                    data-title="<?= htmlspecialchars($song['title']) ?>"
                                    data-artist="<?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?>"
                                    data-audio="<?= $audio ?>"
                                    data-image="<?= $img ?>"
                                    onclick="playSongFromRow(this)">
                                    <td class="text-center text-muted fw-bold"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= $img ?>" 
                                                 width="50" height="50" 
                                                 class="rounded shadow-sm" 
                                                 onerror="this.src='../assets/songs/images/default.jpg'"
                                                 alt="<?= htmlspecialchars($song['title']) ?>">
                                            <div>
                                                <div class="fw-bold text-white"><?= htmlspecialchars($song['title']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($song['artist'] ?? 'Không rõ') ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-link text-danger p-2" 
                                                onclick="event.stopPropagation(); removeFromAlbum(<?= $album_id ?>, <?= $song['id'] ?>, this.closest('tr'))"
                                                title="Xóa khỏi album">
                                            <i class="fas fa-trash-alt fa-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5 my-5">
                    <i class="fas fa-compact-disc fa-8x text-muted mb-4 opacity-20"></i>
                    <h2 class="text-muted fw-light">Album này chưa có bài hát nào</h2>
                    <p class="text-muted mb-4">Hãy thêm những bản nhạc yêu thích của bạn!</p>
                    <button class="btn btn-primary btn-lg px-5 py-3 rounded-pill" 
                            data-bs-toggle="modal" data-bs-target="#addToAlbumModal">
                        Thêm bài hát ngay
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../includes/add_to_album_modal.php';
include __DIR__ . '/../includes/player.php'; 
include __DIR__ . '/../includes/footer.php'; 
?>

<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/script.js"></script>

<style>
    .btn-play-big {
        width: 90px; height: 90px;
        background: linear-gradient(135deg, #00D4FF, #8A2BE2);
        border: none; border-radius: 50%;
        font-size: 2.8rem; color: white;
        box-shadow: 0 20px 60px rgba(0,212,255,0.5);
        transition: all 0.4s ease;
    }
    .btn-play-big:hover { transform: scale(1.15); box-shadow: 0 30px 80px rgba(0,212,255,0.7); }
    .song-row:hover { background: rgba(0,212,255,0.15) !important; transform: translateY(-2px); }
    .text-gradient {
        background: linear-gradient(90deg, #00D4FF, #8A2BE2);
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent; color: transparent;
    }
</style>

<script>
// PHÁT TOÀN BỘ ALBUM
function playAlbum(albumId) {
    // 1. Lấy tất cả các dòng bài hát đang hiển thị
    const rows = document.querySelectorAll('.song-row');

    // 2. Kiểm tra nếu album trống
    if (rows.length === 0) {
        alert("Album này chưa có bài hát nào để phát!");
        return;
    }

    // 3. Tạo playlist từ dữ liệu data- attribute của các dòng HTML
    // Lưu ý: Đảm bảo các thuộc tính data-audio, data-image,... đã được in đúng trong vòng lặp PHP
    window.playlist = Array.from(rows).map(row => ({
        id: row.getAttribute('data-song-id'),
        title: row.getAttribute('data-title'),
        artist: row.getAttribute('data-artist'),
        audio: row.getAttribute('data-audio'),
        image: row.getAttribute('data-image')
    }));

    // 4. Reset index về bài đầu tiên và phát
    window.currentIndex = 0;
    
    // Gọi hàm playSong (Hàm này thường nằm trong file player.php hoặc script.js chính của bạn)
    if (typeof playSong === 'function') {
        playSong(0); // Phát bài ở vị trí 0 trong playlist
        console.log("Đang phát album với " + window.playlist.length + " bài hát.");
    } else {
        console.error("Lỗi: Không tìm thấy hàm playSong(). Kiểm tra file player.php");
    }
}

// --- LOGIC XÓA BÀI HÁT (GIỮ NGUYÊN NHƯ CŨ) ---
function removeFromAlbum(albumId, songId, rowElement) {
    if (!confirm('Xóa bài hát này khỏi album?')) return;

    fetch('../ajax/remove_from_album.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `album_id=${albumId}&song_id=${songId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            // Hiệu ứng mờ dần trước khi xóa
            rowElement.style.transition = "all 0.5s ease";
            rowElement.style.opacity = "0";
            
            setTimeout(() => {
                rowElement.remove();
                
                // Cập nhật số lượng bài hát hiển thị trên Header
                const countEl = document.getElementById('songCount');
                if(countEl) {
                    let count = parseInt(countEl.textContent);
                    countEl.textContent = Math.max(0, count - 1);
                }

                // Cập nhật lại số thứ tự (Cột #)
                document.querySelectorAll('#albumSongsList tr').forEach((tr, i) => {
                    tr.querySelector('td:first-child').textContent = i + 1;
                });

                // Nếu xóa hết thì hiện giao diện trống
                if (document.querySelectorAll('#albumSongsList tr').length === 0) {
                    document.getElementById('albumSongsContainer').innerHTML = `
                        <div class="text-center py-5 my-5">
                            <i class="fas fa-compact-disc fa-8x text-muted mb-4 opacity-20"></i>
                            <h2 class="text-muted fw-light">Album này chưa có bài hát nào</h2>
                            <button class="btn btn-primary btn-lg px-5 py-3 rounded-pill mt-3" 
                                    onclick="window.location.href='album_add_songs.php?id=${albumId}'">
                                Thêm bài hát ngay
                            </button>
                        </div>
                    `;
                }
            }, 500); // Đợi 0.5s cho hiệu ứng xong
            
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa'));
        }
    })
    .catch(() => alert('Lỗi kết nối server!'));
}
</script>