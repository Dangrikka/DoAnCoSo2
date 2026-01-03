<?php
// song_detail.php - FIXED COVER IMAGE & VIETNAMESE
session_start();
require_once '../config/database.php';
require_once '../controllers/SongController.php';
require_once '../controllers/ReviewController.php';

// 1. Kiểm tra ID bài hát
$song_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($song_id <= 0) {
    header("Location: ../views/home.php");
    exit;
}

$songCtrl = new SongController();
$reviewCtrl = new ReviewController();

$song = $songCtrl->getSongById($song_id);
if (!$song) {
    die('<div class="container py-5 text-center text-white"><h3>Bài hát không tồn tại.</h3><a href="home.php" class="btn btn-outline-light mt-3">Quay lại</a></div>');
}

$message = '';

// 2. Xử lý gửi bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);

    if (!empty($comment)) {
        if ($reviewCtrl->addReview($song_id, $_SESSION['user_id'], $rating, $comment)) {
            header("Location: song_detail.php?id=$song_id&review=success");
            exit;
        } else {
            $message = '<div class="alert alert-danger shadow-sm">Lỗi khi gửi bình luận. Vui lòng thử lại.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning shadow-sm">Vui lòng nhập nội dung bình luận.</div>';
    }
}

// Thông báo thành công
if (isset($_GET['review']) && $_GET['review'] === 'success') {
    $message = '<div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-2"></i>Cảm ơn bạn đã đánh giá!</div>';
}

$reviews = $reviewCtrl->getReviewsBySongId($song_id);
$isFavorite = $song['is_favorite'] ?? false;

// Xử lý đường dẫn file (QUAN TRỌNG CHO VIỆC SỬA LỖI ẢNH)
$audioPath = "../assets/songs/audio/" . $song['audio_file'];
// Nếu không có ảnh, dùng ảnh mặc định.
$imageFile = !empty($song['image']) ? $song['image'] : 'default.jpg';
$imagePath = "../assets/songs/images/" . $imageFile;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid px-4 py-5">

        <?= $message ?>

        <div class="song-detail-card p-4 p-md-5 rounded-4 shadow-lg position-relative overflow-hidden">
            <div class="detail-bg" style="background-image: url('<?= htmlspecialchars($imagePath) ?>');"></div>
            
            <div class="row align-items-center position-relative z-2">
                <div class="col-md-4 col-lg-3 text-center mb-4 mb-md-0">
                    <div class="cover-wrapper">
                        <img src="<?= htmlspecialchars($imagePath) ?>" 
                             alt="<?= htmlspecialchars($song['title']) ?>" 
                             class="img-fluid rounded-4 shadow-2xl main-cover"
                             onerror="this.src='../assets/songs/images/default.jpg'">
                        <div class="playing-indicator"><i class="fas fa-chart-bar"></i></div>
                    </div>
                </div>

                <div class="col-md-8 col-lg-9 text-center text-md-start">
                    <h5 class="text-uppercase text-info fw-bold ls-2 mb-2">Bài hát</h5>
                    <h1 class="display-4 fw-bold text-white mb-2 text-shadow"><?= htmlspecialchars($song['title']) ?></h1>
                    <h3 class="h2 text-white-50 mb-4"><?= htmlspecialchars($song['artist']) ?></h3>

                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-md-start action-buttons">
                        <button class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-glow btn-play-now" onclick="playSongNow()">
                            <i class="fas fa-play me-2"></i> Phát Ngay
                        </button>

                        <button class="btn btn-outline-light btn-icon rounded-circle favorite-btn" 
                                data-song-id="<?= $song['id'] ?>" title="Yêu thích">
                            <i class="fa<?= $isFavorite ? 's text-danger' : 'r' ?> fa-heart"></i>
                        </button>

                        <a href="album_select.php?song_id=<?= $song['id'] ?>" class="btn btn-outline-light btn-icon rounded-circle" title="Thêm vào Album">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5 justify-content-center">
            <div class="col-lg-10">
                <h3 class="text-white mb-4 border-start border-4 border-info ps-3">
                    <i class="fas fa-comments me-2 text-info"></i>Bình luận & Đánh giá <span class="text-muted fs-5">(<?= count($reviews) ?>)</span>
                </h3>

                <?php if (isset($_SESSION['user_id'])): 
                    $myAvatar = !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : 'avatar.jpg';
                ?>
                <div class="review-form-card p-4 rounded-4 mb-5">
                    <form method="post">
                        <div class="d-flex gap-3">
                            <div class="flex-shrink-0">
                                <img src="../assets/songs/images/<?= htmlspecialchars($myAvatar) ?>" 
                                     class="rounded-circle border border-info" 
                                     width="60" height="60" 
                                     style="object-fit: cover;"
                                     onerror="this.src='../assets/songs/images/avatar.jpg'">
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-3">
                                    <label class="text-white-50 small me-3">Đánh giá:</label>
                                    <div class="star-rating" id="star-rating-input">
                                        <i class="fas fa-star" data-value="1"></i>
                                        <i class="fas fa-star" data-value="2"></i>
                                        <i class="fas fa-star" data-value="3"></i>
                                        <i class="fas fa-star" data-value="4"></i>
                                        <i class="fas fa-star" data-value="5"></i>
                                    </div>
                                    <input type="hidden" id="rating-value" name="rating" value="5">
                                </div>
                                
                                <div class="form-floating mb-3">
                                    <textarea name="comment" class="form-control bg-dark text-white border-secondary" placeholder="Nhập bình luận" id="floatingTextarea" style="height: 100px"></textarea>
                                    <label for="floatingTextarea" class="text-secondary">Chia sẻ cảm nghĩ của bạn về bài hát này...</label>
                                </div>
                                
                                <div class="text-end">
                                    <button class="btn btn-info rounded-pill px-4 fw-bold">Gửi đánh giá</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <?php else: ?>
                    <div class="alert alert-dark text-center py-4 border-secondary border-dashed">
                        <i class="fas fa-lock mb-2 d-block fs-4 text-secondary"></i>
                        Vui lòng <a href="../login.php" class="text-info fw-bold text-decoration-none">Đăng nhập</a> để tham gia bình luận.
                    </div>
                <?php endif; ?>

                <div class="reviews-list">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="far fa-comment-dots fa-3x mb-3 opacity-25"></i>
                            <p>Chưa có đánh giá nào. Hãy là người đầu tiên!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): 
                            $uAvatar = !empty($review['avatar']) ? $review['avatar'] : 'avatar.jpg';
                            $uAvatarPath = "../assets/songs/images/" . htmlspecialchars($uAvatar);
                        ?>
                        <div class="d-flex gap-3 mb-4 review-item animate-fade-up">
                            <div class="flex-shrink-0">
                                <img src="<?= $uAvatarPath ?>" 
                                     class="rounded-circle shadow-sm border border-secondary" 
                                     width="50" height="50" 
                                     style="object-fit: cover;" 
                                     onerror="this.src='../assets/songs/images/avatar.jpg'">
                            </div>

                            <div class="flex-grow-1">
                                <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="text-info fw-bold mb-0"><?= htmlspecialchars($review['username']) ?></h6>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></small>
                                    </div>
                                    <div class="mb-2 text-warning small">
                                        <?php for ($i=1; $i<=5; $i++): ?>
                                            <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="text-white-50 mb-0 review-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/player.php'; ?>
<?php include '../includes/footer.php'; ?>

<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* --- CSS CHO TRANG CHI TIẾT --- */
.song-detail-card {
    background: rgba(20, 20, 40, 0.6);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-bg {
    position: absolute; inset: 0;
    background-size: cover; background-position: center;
    filter: blur(60px) brightness(0.3);
    z-index: 0; pointer-events: none;
}

.cover-wrapper {
    position: relative;
    display: inline-block;
    transition: transform 0.3s ease;
}
.cover-wrapper:hover { transform: scale(1.02); }

.main-cover {
    width: 100%; max-width: 300px; aspect-ratio: 1/1; object-fit: cover;
}

.text-shadow { text-shadow: 0 4px 15px rgba(0,0,0,0.5); }
.shadow-glow { box-shadow: 0 0 25px rgba(0, 212, 255, 0.4); }

.btn-icon {
    width: 54px; height: 54px; display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; transition: all 0.3s;
}
.btn-icon:hover { background: rgba(255,255,255,0.1); transform: translateY(-3px); }

/* --- CSS BÌNH LUẬN --- */
.review-form-card {
    background: linear-gradient(145deg, rgba(30,30,50,0.8), rgba(20,20,30,0.9));
    border: 1px solid rgba(255,255,255,0.05);
}

.star-rating i {
    cursor: pointer; color: #444; font-size: 1.2rem; transition: color 0.2s;
}
.star-rating i.selected, .star-rating i:hover { color: #ffc107; }

.review-text { line-height: 1.6; }

@media (max-width: 768px) {
    .display-4 { font-size: 2.5rem; }
}
</style>

<script src="../assets/js/script.js"></script>

<script>
// 1. XỬ LÝ ĐÁNH GIÁ SAO
document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('#star-rating-input i');
    const ratingInput = document.getElementById('rating-value');
    let currentRating = 5;

    function highlightStars(rating) {
        stars.forEach(star => {
            const val = parseInt(star.dataset.value);
            star.classList.toggle('selected', val <= rating);
            if(val <= rating) {
                star.classList.remove('far'); star.classList.add('fas');
            } else {
                star.classList.remove('fas'); star.classList.add('far');
            }
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', () => highlightStars(star.dataset.value));
        star.addEventListener('mouseout', () => highlightStars(currentRating));
        star.addEventListener('click', () => {
            currentRating = star.dataset.value;
            ratingInput.value = currentRating;
            highlightStars(currentRating);
        });
    });
    highlightStars(5); // Khởi tạo mặc định 5 sao
});

// 2. XỬ LÝ YÊU THÍCH (AJAX)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.favorite-btn');
    if (!btn) return;

    const songId = btn.dataset.songId;
    const icon = btn.querySelector('i');

    // Hiệu ứng click
    icon.style.transform = "scale(1.4)";
    setTimeout(() => icon.style.transform = "scale(1)", 200);

    fetch('../ajax/favorite_toggle.php', {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "song_id=" + songId
    })
    .then(res => res.json())
    .then(data => {
        if (data.action === 'added') {
            icon.className = "fas fa-heart text-danger";
        } else {
            icon.className = "far fa-heart";
        }
    })
    .catch(err => console.error("Lỗi kết nối server:", err));
});

// 3. XỬ LÝ PHÁT NHẠC (SỬA LỖI ẢNH BÌA)
window.playSongNow = function() {
    // Sử dụng PHP để tạo object JSON an toàn, tránh lỗi cú pháp JS do ký tự lạ
    const songData = <?= json_encode([
        'id' => $song['id'],
        'title' => $song['title'],
        'artist' => $song['artist'],
        'audio' => $audioPath,
        'image' => $imagePath // Đường dẫn đã được xử lý ở trên PHP
    ]) ?>;

    console.log("Playing song:", songData); // Debug để kiểm tra đường dẫn trong Console

    if (typeof window.playDirectSong === 'function') {
        window.playDirectSong(songData);
    } else if (typeof window.buildPlaylistFromDOM === 'function') {
        // Fallback: Tạo thẻ ẩn để Player đọc dữ liệu
        const tempCard = document.createElement('div');
        tempCard.className = 'song-card d-none';
        
        // Gán dataset chính xác
        tempCard.dataset.id = songData.id;
        tempCard.dataset.title = songData.title;
        tempCard.dataset.artist = songData.artist;
        tempCard.dataset.audio = songData.audio;
        tempCard.dataset.image = songData.image; // Quan trọng: Truyền đúng đường dẫn ảnh

        document.body.appendChild(tempCard);
        
        // Logic thêm vào playlist
        if (window.playlist) {
            const exists = window.playlist.findIndex(s => String(s.id) === String(songData.id));
            if (exists !== -1) {
                window.playSong(exists);
            } else {
                window.playlist.unshift({...songData, _element: tempCard});
                window.playSong(0);
            }
        }
        setTimeout(() => tempCard.remove(), 1000);
    } else {
        console.error("Không tìm thấy trình phát nhạc (script.js chưa load).");
        alert("Trình phát nhạc chưa sẵn sàng. Vui lòng tải lại trang.");
    }
};

// Tự động phát nếu có tham số trên URL
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('autoplay') === 'true') {
        setTimeout(playSongNow, 500);
    }
})();
</script>