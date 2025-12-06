<?php

class ReviewController {
    private $review;
    private $favorite;

    public function __construct() {
        // Đảm bảo kết nối DB đã có (từ config/database.php)
        if (!isset($GLOBALS['db_conn'])) {
            require_once __DIR__ . '/../config/database.php';
        }

        require_once __DIR__ . '/../models/Review.php';

        $this->review   = new Review();
    }

    // === THÊM ĐÁNH GIÁ MỚI ===
    public function addReview($song_id, $user_id, $rating, $comment = '') {
        $song_id = (int)$song_id;
        $user_id = (int)$user_id;
        $rating  = (int)$rating;

        // Validate đầu vào
        if ($song_id <= 0 || $user_id <= 0) {
            return ['status' => 'error', 'message' => 'ID không hợp lệ'];
        }
        if ($rating < 1 || $rating > 5) {
            return ['status' => 'error', 'message' => 'Điểm đánh giá phải từ 1 đến 5'];
        }

        $result = $this->review->create($song_id, $user_id, $rating, trim($comment));

        return $result
            ? ['status' => 'success', 'message' => 'Cảm ơn bạn đã đánh giá!']
            : ['status' => 'error', 'message' => 'Lỗi khi lưu đánh giá'];
    }

    // === YÊU THÍCH / BỎ YÊU THÍCH (DÀNH CHO FORM HOẶC AJAX RIÊNG) ===
    public function toggleFavorite($user_id, $song_id) {
        $user_id = (int)$user_id;
        $song_id = (int)$song_id;

        if ($user_id <= 0 || $song_id <= 0) {
            return ['status' => 'error', 'message' => 'ID không hợp lệ'];
        }

        $action = $this->favorite->toggle($user_id, $song_id);

        if ($action === 'added') {
            return ['status' => 'success', 'action' => 'added', 'message' => 'Đã thêm vào yêu thích'];
        } elseif ($action === 'removed') {
            return ['status' => 'success', 'action' => 'removed', 'message' => 'Đã xóa khỏi yêu thích'];
        } else {
            return ['status' => 'error', 'message' => 'Lỗi hệ thống'];
        }
    }

    // === LẤY DANH SÁCH YÊU THÍCH CỦA USER ===
    public function getUserFavorites($user_id) {
        $user_id = (int)$user_id;
        if ($user_id <= 0) return [];

        $favorites = $this->favorite->getByUserId($user_id);
        return is_array($favorites) ? $favorites : [];
    }

    // === LẤY TẤT CẢ ĐÁNH GIÁ CỦA MỘT BÀI HÁT ===
    public function getReviewsBySongId($song_id) {
        $song_id = (int)$song_id;
        if ($song_id <= 0) return [];

        $reviews = $this->review->getBySongId($song_id);
        return is_array($reviews) ? $reviews : [];
    }

    // === KIỂM TRA USER ĐÃ YÊU THÍCH BÀI NÀY CHƯA ===
    public function isFavorite($user_id, $song_id) {
        $user_id = (int)$user_id;
        $song_id = (int)$song_id;

        if ($user_id <= 0 || $song_id <= 0) return false;

        return $this->favorite->isFavorite($user_id, $song_id);
    }

    // === LẤY ĐIỂM TRUNG BÌNH CỦA BÀI HÁT ===
    public function getAverageRating($song_id) {
        $song_id = (int)$song_id;
        if ($song_id <= 0) return 0;

        $reviews = $this->getReviewsBySongId($song_id);
        if (empty($reviews)) return 0;

        $total = 0;
        foreach ($reviews as $r) {
            $total += (int)$r['rating'];
        }
        return round($total / count($reviews), 1);
    }
}
?>