<?php
session_start();
require_once '../config/database.php';

// 1. Lấy ID bài hát và kiểm tra
$song_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($song_id <= 0) {
    die("ID bài hát không hợp lệ.");
}

// 2. Nạp Controller
require_once '../controllers/SongController.php';
require_once '../controllers/ReviewController.php';

$songCtrl = new SongController();
$reviewCtrl = new ReviewController();

// 3. Lấy thông tin bài hát
$song = $songCtrl->getSongById($song_id);
if (!$song) {
    die("Không tìm thấy bài hát.");
}

$message = '';

// 4. Xử lý khi người dùng gửi bình luận mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $comment = trim($_POST['comment'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5); // Mặc định là 5 sao

    if (!empty($comment)) {
        if ($reviewCtrl->addReview($song_id, $_SESSION['user_id'], $rating, $comment)) {
            // Tải lại trang để hiển thị bình luận mới và tránh gửi lại form
            header("Location: song_detail.php?id=$song_id");
            exit;
        } else {
            $message = '<div class="alert alert-danger">Lỗi khi gửi bình luận. Vui lòng thử lại.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Vui lòng nhập nội dung bình luận.</div>';
    }
}

// 5. Lấy danh sách bình luận
$reviews = $reviewCtrl->getReviewsBySongId($song_id);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="container-fluid px-4 py-5">

        <?= $message ?>

        <!-- Phần thông tin bài hát -->
        <div class="d-flex flex-column flex-md-row align-items-center gap-4 gap-md-5 mb-5">
            <div class="song-detail-cover">
                <img src="../assets/songs/<?php echo htmlspecialchars($song['image'] ?? 'default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($song['title']); ?>"
                     class="rounded-4 shadow-lg">
            </div>
            <div class="text-center text-md-start">
                <h1 class="display-2 fw-bold text-gradient mb-2">
                    <?php echo htmlspecialchars($song['title']); ?>
                </h1>
                <p class="fs-2 text-white opacity-75 mb-4">
                    <?php echo htmlspecialchars($song['artist']); ?>
                </p>
                <button class="btn btn-primary btn-lg rounded-pill px-5 py-3" onclick="playSongNow()">
                    <i class="fas fa-play me-2"></i> Phát ngay
                </button>
            </div>
        </div>

        <!-- Phần bình luận -->
        <div class="comments-section mt-5">
            <h2 class="text-gradient mb-4"><i class="fas fa-comments me-2"></i> Bình luận & Đánh giá</h2>

            <!-- Form gửi bình luận -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="post" class="mb-5">
                    <!-- Hệ thống đánh giá 5 sao -->
                    <div class="mb-3">
                        <label class="form-label text-white-50">Đánh giá của bạn:</label>
                        <div class="star-rating" id="star-rating-input">
                            <i class="fas fa-star" data-value="1"></i>
                            <i class="fas fa-star" data-value="2"></i>
                            <i class="fas fa-star" data-value="3"></i>
                            <i class="fas fa-star" data-value="4"></i>
                            <i class="fas fa-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" value="5">
                    </div>
                    <div class="mb-3">
                        <textarea name="comment" class="form-control" rows="4" placeholder="Chia sẻ cảm nhận của bạn về bài hát này..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Gửi bình luận</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <a href="../login.php">Đăng nhập</a> để bình luận về bài hát này.
                </div>
            <?php endif; ?>

            <!-- Danh sách bình luận -->
            <div class="comment-list">
                <?php if (empty($reviews)): ?>
                    <p class="text-muted text-center fst-italic">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <!-- Hiển thị sao đánh giá -->
                                <div class="comment-rating me-3">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo ($i <= $review['rating']) ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <strong class="text-primary"><?php echo htmlspecialchars($review['username']); ?></strong>
                                <small class="text-muted ms-auto"><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></small>
                            </div>
                            <p class="mb-0 mt-2">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
include '../includes/player.php'; 
include '../includes/footer.php'; 
?>

<!-- Script riêng cho trang này -->
<script src="../assets/js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const starsContainer = document.getElementById('star-rating-input');
    if (!starsContainer) return;

    const stars = starsContainer.querySelectorAll('.fa-star');
    const ratingInput = document.getElementById('rating-value');
    let currentRating = parseInt(ratingInput.value);

    function updateStars(rating) {
        stars.forEach(star => {
            star.classList.toggle('selected', star.dataset.value <= rating);
        });
    }

    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            updateStars(this.dataset.value);
        });

        star.addEventListener('mouseout', function() {
            updateStars(currentRating);
        });

        star.addEventListener('click', function() {
            currentRating = this.dataset.value;
            ratingInput.value = currentRating;
            updateStars(currentRating);
        });
    });

    // Khởi tạo trạng thái ban đầu
    updateStars(currentRating);
});
</script>
<script>
function playSongNow() {
    // Tạo một đối tượng bài hát tạm thời để truyền vào trình phát
    const songData = {
        id: '<?php echo $song['id']; ?>',
        title: '<?php echo htmlspecialchars(addslashes($song['title'])); ?>',
        artist: '<?php echo htmlspecialchars(addslashes($song['artist'])); ?>',
        audio: '../assets/songs/audio/<?php echo htmlspecialchars($song['audio_file']); ?>',
        image: '../assets/songs/<?php echo htmlspecialchars($song['image'] ?? 'default.jpg'); ?>'
    };

    // Giả lập một song-card để script.js có thể đọc được
    const fakeCard = document.createElement('div');
    fakeCard.classList.add('song-card');
    fakeCard.dataset.songId = songData.id;
    fakeCard.dataset.title = songData.title;
    fakeCard.dataset.artist = songData.artist;
    fakeCard.dataset.audio = songData.audio;
    fakeCard.dataset.image = songData.image;

    // Thêm tạm vào body và ẩn đi
    fakeCard.style.display = 'none';
    document.body.appendChild(fakeCard);

    // Gọi hàm buildSongList và playSong từ script.js
    // Hàm này cần được truy cập toàn cục
    if (typeof buildSongList === 'function' && typeof playSong === 'function') {
        buildSongList(); // Cập nhật danh sách bài hát
        // Tìm index của bài hát vừa thêm
        const newSongIndex = songList.findIndex(s => s.id === songData.id);
        if (newSongIndex !== -1) {
            playSong(newSongIndex);
        }
    } else {
        // Fallback nếu các hàm không có sẵn
        const audioPlayer = document.getElementById('audio');
        if(audioPlayer) {
            audioPlayer.src = songData.audio;
            audioPlayer.play();
        }
    }

    // Xóa card giả sau khi dùng
    document.body.removeChild(fakeCard);
}
</script>

<style>
.song-detail-cover img {
    width: 280px;
    height: 280px;
    object-fit: cover;
    border: 4px solid rgba(0,212,255,0.3);
}

.comments-section {
    background: rgba(10, 10, 30, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid var(--border-glow);
    border-radius: var(--border-radius);
    padding: 2rem 2.5rem;
}

.comments-section .form-control {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.2);
    color: white;
}

.comments-section .form-control:focus {
    background: rgba(255,255,255,0.1);
    border-color: var(--primary);
    box-shadow: 0 0 20px var(--shadow-blue);
}

.comment-item {
    background: rgba(0,212,255,0.05);
    border-radius: 16px;
    padding: 1rem 1.5rem;
    margin-bottom: 1rem;
    border-left: 3px solid var(--primary);
}
</style>