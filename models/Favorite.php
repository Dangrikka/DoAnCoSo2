<?php
// models/Favorite.php

class Favorite {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    // Kiểm tra xem user đã like bài này chưa
    public function isFavorite($user_id, $song_id) {
        $sql = "SELECT 1 FROM favorites WHERE user_id = ? AND song_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $song_id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // Toggle: Nếu chưa like thì thêm, like rồi thì xóa
    public function toggle($user_id, $song_id) {
        if ($this->isFavorite($user_id, $song_id)) {
            // Đã like -> Xóa (Unlike)
            $sql = "DELETE FROM favorites WHERE user_id = ? AND song_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $song_id);
            $stmt->execute();
            return 'removed';
        } else {
            // Chưa like -> Thêm (Like)
            $sql = "INSERT INTO favorites (user_id, song_id) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $song_id);
            $stmt->execute();
            return 'added';
        }
    }

    // Lấy danh sách bài hát yêu thích của User
    public function getByUserId($user_id) {
        $sql = "SELECT s.* FROM songs s 
                JOIN favorites f ON s.id = f.song_id 
                WHERE f.user_id = ? 
                ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>