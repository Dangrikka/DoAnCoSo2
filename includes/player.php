<!-- includes/player.php - PLAYER SIÊU ĐẸP 2025 - HOÀN HẢO TUYỆT ĐỐI -->
<div class="player" id="musicPlayer">
    <!-- TRÁI: Ảnh bìa + Tên bài + Yêu thích -->
    <div class="player-left">
        <div class="player-cover">
            <img src="../assets/songs/images/default1.jpg" alt="Cover" id="playerCover">
        </div>
        <div class="player-info">
            <div class="player-song-title fw-bold" id="playerTitle">Chưa phát nhạc</div>
            <div class="player-artist-name text-muted small" id="playerArtist">Nhạc Hay 2025</div>
        </div>
        <button class="btn-like ms-3" id="playerFavoriteBtn" title="Yêu thích" data-song-id="">
            <i class="far fa-heart"></i>
        </button>
    </div>

    <!-- GIỮA: Điều khiển + Thanh tiến trình -->
    <div class="player-center">
        <div class="player-controls">
            <button class="shuffle-btn" title="Phát ngẫu nhiên">
                <i class="fas fa-random"></i>
            </button>
            <button class="prev-btn" title="Bài trước">
                <i class="fas fa-step-backward"></i>
            </button>
            <button class="play-pause-btn" title="Phát / Tạm dừng">
                <i class="fas fa-play"></i>
            </button>
            <button class="next-btn" title="Bài tiếp">
                <i class="fas fa-step-forward"></i>
            </button>
            <button class="repeat-btn" title="Lặp lại">
                <i class="fas fa-redo"></i>
            </button>
        </div>

        <div class="progress-section">
            <span class="current-time text-muted small">0:00</span>
            <div class="progress-container">
                <div class="progress"></div>
            </div>
            <span class="duration text-muted small">0:00</span>
        </div>
    </div>

    <!-- PHẢI: Âm lượng -->
    <div class="player-right">
        <div class="volume-control">
            <button class="volume-btn" title="Âm lượng">
                <i class="fas fa-volume-up"></i>
            </button>
            <input type="range" class="volume-slider" min="0" max="1" step="0.01" value="0.7">
        </div>
    </div>

    <!-- Wave animation khi đang phát -->
    <div class="wave-animation">
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
        <div class="wave-bar"></div>
    </div>
</div>

<!-- Audio ẩn -->
<audio id="audio" preload="metadata"></audio>

<!-- GỌI SCRIPT CHÍNH - CHỈ CẦN 1 DÒNG NÀY LÀ ĐỦ -->
<script src="../assets/js/script.js"></script>