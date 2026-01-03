<?php
// controllers/ReviewController.php

class ReviewController {
    private $review;
    private $favorite;

    public function __construct() {
        // 1. Kết nối Database
        if (!isset($GLOBALS['db_conn'])) {
            require_once __DIR__ . '/../config/database.php';
        }

        // 2. Load Model Review
        require_once __DIR__ . '/../models/Review.php';
        $this->review = new Review();

        // 3. Load Model Favorite (ĐÂY LÀ PHẦN QUAN TRỌNG ĐỂ SỬA LỖI)
        if (file_exists(__DIR__ . '/../models/Favorite.php')) {
            require_once __DIR__ . '/../models/Favorite.php';
            $this->favorite = new Favorite();
        } else {
            // Nếu không tìm thấy file, gán null để tránh lỗi crash trang
            $this->favorite = null;
            error_log("Lỗi: Không tìm thấy file models/Favorite.php");
        }
    }

    // ... (Giữ nguyên các hàm addReview, toggleFavorite... như cũ) ...
    
    // Copy lại các hàm dưới đây để đảm bảo code đầy đủ:

    public function addReview($song_id, $user_id, $rating, $comment = '') {
        $song_id = (int)$song_id;
        $user_id = (int)$user_id;
        $rating  = (int)$rating;

        if ($song_id <= 0 || $user_id <= 0 || $rating < 1 || $rating > 5) {
            return false;
        }
        return $this->review->create($song_id, $user_id, $rating, trim($comment));
    }

    public function toggleFavorite($user_id, $song_id) {
        if (!$this->favorite) return ['status' => 'error', 'message' => 'Chức năng yêu thích đang bảo trì (Thiếu Model)'];

        $user_id = (int)$user_id;
        $song_id = (int)$song_id;
        
        $action = $this->favorite->toggle($user_id, $song_id);

        if ($action === 'added') {
            return ['status' => 'success', 'action' => 'added', 'message' => 'Đã thêm vào yêu thích'];
        } else {
            return ['status' => 'success', 'action' => 'removed', 'message' => 'Đã xóa khỏi yêu thích'];
        }
    }

    public function getReviewsBySongId($song_id) {
        return $this->review->getBySongId((int)$song_id);
    }
}
?>