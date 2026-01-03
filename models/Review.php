<?php
// models/Review.php
class Review {
    private $conn;

    public function __construct() {
        // Kết nối CSDL (đảm bảo biến $conn tồn tại từ config/database.php)
        global $conn;
        $this->conn = $conn;
    }

    // Thêm mới hoặc Cập nhật đánh giá
    public function create($song_id, $user_id, $rating, $comment = '') {
        $sql = "INSERT INTO reviews (song_id, user_id, rating, comment) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = ?, comment = ?";
        
        $stmt = $this->conn->prepare($sql);
        // Lưu ý: rating là số nguyên (i), comment là chuỗi (s)
        // Thứ tự: song_id(i), user_id(i), rating(i), comment(s), update_rating(i), update_comment(s)
        $stmt->bind_param("iiisis", $song_id, $user_id, $rating, $comment, $rating, $comment);
        
        return $stmt->execute();
    }

    // Lấy danh sách đánh giá theo bài hát
    public function getBySongId($song_id) {
        // QUAN TRỌNG: Đã thêm u.avatar vào câu lệnh SELECT
        $sql = "SELECT r.*, u.username, u.avatar 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.song_id = ? 
                ORDER BY r.created_at DESC";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $song_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}