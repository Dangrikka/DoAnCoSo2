<?php
class PlaylistSong {
    private $conn;

    public function __construct() {
        // Sử dụng biến $conn toàn cục đã được nạp ở các file view
        global $conn;
        $this->conn = $conn;
    }

    public function add($playlist_id, $song_id) {
        $sql = "INSERT IGNORE INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $playlist_id, $song_id);
        return $stmt->execute();
    }

    public function getSongs($playlist_id) {
        $sql = "SELECT s.* FROM songs s 
                JOIN playlist_songs ps ON s.id = ps.song_id 
                WHERE ps.playlist_id = ? ORDER BY ps.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $playlist_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}