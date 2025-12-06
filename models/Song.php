<?php
// models/Song.php

class Song {
    private $conn;
    private $table = 'songs';

    public function __construct() {
        global $conn;
        $this->conn = $conn;

        if (!$this->conn) {
            die("Lỗi kết nối database trong Song model!");
        }
    }

    // TRANG CHỦ – LẤY TẤT CẢ BÀI HÁT + TRẠNG THÁI YÊU THÍCH
    public function getAllWithFavorites($userId = null) {
        $sql = "SELECT s.*, 
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                ORDER BY s.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $uid = $userId !== null ? (int)$userId : 0;
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // TÌM KIẾM – SIÊU NHANH, CHÍNH XÁC
    public function search($keyword, $userId = null) {
        $keyword = '%' . $this->conn->real_escape_string(trim($keyword)) . '%';

        $sql = "SELECT s.*, 
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                WHERE s.title LIKE ? OR s.artist LIKE ?
                ORDER BY 
                    CASE 
                        WHEN s.title LIKE ? THEN 1
                        WHEN s.artist LIKE ? THEN 2
                        ELSE 3 
                    END,
                    s.title ASC
                LIMIT 50";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $uid = $userId !== null ? (int)$userId : 0;
        $stmt->bind_param('issss', $uid, $keyword, $keyword, $keyword, $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // CHI TIẾT BÀI HÁT
    public function getById($id, $userId = null) {
        $sql = "SELECT s.*, 
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                WHERE s.id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $uid = $userId !== null ? (int)$userId : 0;
        $stmt->bind_param('ii', $uid, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $song = $result->fetch_assoc();
        $stmt->close();
        return $song;
    }

    // TRANG YÊU THÍCH CỦA NGƯỜI DÙNG
    public function getFavoriteSongs($userId) {
        $sql = "SELECT s.*, f.created_at AS favorited_at
                FROM {$this->table} s
                JOIN favorites f ON s.id = f.song_id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // BẢNG XẾP HẠNG THEO LƯỢT NGHE (SIÊU HOT 2025)
    public function getTopByPlayCount($userId = null, $limit = 20) {
        $sql = "SELECT s.*, 
                       COALESCE(pc.play_count, 0) AS play_count,
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN play_counts pc ON s.id = pc.song_id
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                GROUP BY s.id
                ORDER BY play_count DESC, s.created_at DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $uid = $userId !== null ? (int)$userId : 0;
        $limit = (int)$limit;
        $stmt->bind_param('ii', $uid, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // TĂNG LƯỢT NGHE KHI NGƯỜI DÙNG PHÁT BÀI HÁT (GỌI TỪ AJAX)
    public function incrementPlayCount($song_id) {
        $song_id = (int)$song_id;

        $stmt = $this->conn->prepare("
            INSERT INTO play_counts (song_id, play_count) 
            VALUES (?, 1) 
            ON DUPLICATE KEY UPDATE 
                play_count = play_count + 1,
                last_played = NOW()
        ");
        $stmt->bind_param('i', $song_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // THÊM BÀI HÁT MỚI (ADMIN)
    public function create($title, $artist, $audio_file, $image_file = 'default.jpg') {
        $title = trim($title);
        $artist = trim($artist);
        $audio_file = trim($audio_file);
        $image_file = $image_file ?: 'default.jpg';

        if (empty($title) || empty($artist) || empty($audio_file)) {
            return false;
        }

        $stmt = $this->conn->prepare("INSERT INTO songs (title, artist, audio_url, image_url, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssss', $title, $artist, $audio_file, $image_file);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // CẬP NHẬT BÀI HÁT (ADMIN)
    public function update($id, $title, $artist, $audio_file = null, $image_file = null) {
        $sql = "UPDATE {$this->table} SET title = ?, artist = ?";
        $types = 'ss';
        $params = [$title, $artist];

        if ($audio_file !== null) {
            $sql .= ", audio_url = ?";
            $types .= 's';
            $params[] = $audio_file;
        }
        if ($image_file !== null) {
            $sql .= ", image_url = ?";
            $types .= 's';
            $params[] = $image_file;
        }

        $sql .= " WHERE id = ?";
        $types .= 'i';
        $params[] = $id;

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // XÓA BÀI HÁT (ADMIN)
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // NÚT TRÁI TIM – YÊU THÍCH / BỎ YÊU THÍCH (AJAX)
    public function toggleFavorite($song_id, $user_id) {
        $song_id = (int)$song_id;
        $user_id = (int)$user_id;

        // Kiểm tra đã yêu thích chưa
        $check = $this->conn->prepare("SELECT 1 FROM favorites WHERE song_id = ? AND user_id = ? LIMIT 1");
        $check->bind_param('ii', $song_id, $user_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            // XÓA YÊU THÍCH
            $stmt = $this->conn->prepare("DELETE FROM favorites WHERE song_id = ? AND user_id = ?");
            $stmt->bind_param('ii', $song_id, $user_id);
            $stmt->execute();
            $stmt->close();
            return 'removed';
        } else {
            // THÊM YÊU THÍCH
            $stmt = $this->conn->prepare("INSERT INTO favorites (song_id, user_id, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param('ii', $song_id, $user_id);
            $stmt->execute();
            $stmt->close();
            return 'added';
        }
    }

    // THÊM MỚI – HÀM DUY NHẤT BẠN ĐANG THIẾU CHO ADMIN
    public function getAllAdmin() {
        $sql = "SELECT s.*, COALESCE(pc.play_count, 0) AS play_count
                FROM songs s
                LEFT JOIN play_counts pc ON s.id = pc.song_id
                ORDER BY s.created_at DESC";

        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>