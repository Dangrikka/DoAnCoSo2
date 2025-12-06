<?php
// models/Review.php
class Review {
    private $conn;

    public function __construct() {
        // Sử dụng biến $conn toàn cục đã được nạp ở các file view
        global $conn;
        $this->conn = $conn;
    }

    public function create($song_id, $user_id, $rating, $comment = '') {
        $sql = "INSERT INTO reviews (song_id, user_id, rating, comment) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE rating = ?, comment = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiisss", $song_id, $user_id, $rating, $comment, $rating, $comment);
        return $stmt->execute();
    }

    public function getBySongId($song_id) {
        $sql = "SELECT r.*, u.username FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.song_id = ? ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $song_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}