<?php

class Song {
    private $conn;
    private $table = 'songs';

    public function __construct() {
        global $conn;
        $this->conn = $conn;

        if (!$this->conn) {
            die("Lỗi kết nối database!");
        }
    }

    // ====== LẤY TẤT CẢ BÀI HÁT (dùng khi thêm vào ALBUM) ======
    public function getAllSongs() {
        $sql = "SELECT s.*, 
                       COALESCE(pc.play_count, 0) AS play_count
                FROM {$this->table} s
                LEFT JOIN play_counts pc ON s.id = pc.song_id
                ORDER BY s.created_at DESC";

        $res = $this->conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    // ====== PHÂN TRANG ======
    public function getSongsByPage($userId, $limit, $offset) {
        $uid = $userId !== null ? (int)$userId : 0;
        $limit = (int)$limit;
        $offset = (int)$offset;

        $sql = "SELECT s.*,
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                ORDER BY s.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param('iii', $uid, $limit, $offset);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function countSongs($userId = null) {
        $res = $this->conn->query("SELECT COUNT(*) AS cnt FROM {$this->table}");
        if (!$res) return 0;
        return (int)$res->fetch_assoc()['cnt'];
    }

    // ====== SEARCH ======
    public function search($keyword, $userId = null) {
        $keywordEsc = '%' . $this->conn->real_escape_string(trim($keyword)) . '%';
        $uid = $userId !== null ? (int)$userId : 0;

        $sql = "SELECT s.*,
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                WHERE s.title LIKE ? OR s.artist LIKE ?
                ORDER BY s.title ASC
                LIMIT 50";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param('iss', $uid, $keywordEsc, $keywordEsc);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // ====== GET BY ID ======
    public function getById($id, $userId = null) {
        $uid = $userId !== null ? (int)$userId : 0;

        $sql = "SELECT s.*,
                       COALESCE(pc.play_count, 0) AS play_count,
                       IF(f.user_id IS NOT NULL, 1, 0) AS is_favorite
                FROM {$this->table} s
                LEFT JOIN play_counts pc ON s.id = pc.song_id
                LEFT JOIN favorites f ON s.id = f.song_id AND f.user_id = ?
                WHERE s.id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;

        $stmt->bind_param('ii', $uid, $id);
        $stmt->execute();
        $song = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $song;
    }

    // ====== YÊU THÍCH ======
    public function getFavoriteSongs($userId) {
        $stmt = $this->conn->prepare("
            SELECT s.*, f.created_at AS favorited_at
            FROM {$this->table} s
            JOIN favorites f ON s.id = f.song_id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        if (!$stmt) return [];

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    // ====== TOP + PLAY COUNT ======
    public function getTopByPlayCount($userId = null, $limit = 20) {
        $uid = $userId !== null ? (int)$userId : 0;
        $limit = (int)$limit;

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

        $stmt->bind_param('ii', $uid, $limit);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function incrementPlayCount($song_id) {
        $stmt = $this->conn->prepare("
            INSERT INTO play_counts (song_id, play_count, last_played) 
            VALUES (?, 1, NOW())
            ON DUPLICATE KEY UPDATE play_count = play_count + 1, last_played = NOW()");
        if (!$stmt) return false;

        $stmt->bind_param('i', $song_id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    // ====== CRUD ======
    public function create($title, $artist, $audio_file, $image_file = 'default.jpg') {
    $stmt = $this->conn->prepare("
        INSERT INTO songs (title, artist, audio_file, image, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    if (!$stmt) return false;

    $stmt->bind_param('ssss', $title, $artist, $audio_file, $image_file);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
    }


    public function update($id, $title, $artist, $audio_file = null, $image_file = null) {
    $sql = "UPDATE songs SET title=?, artist=?";
    $types = 'ss';
    $params = [$title, $artist];

    if ($audio_file !== null) {
        $sql .= ", audio_file=?";
        $types .= 's';
        $params[] = $audio_file;
    }

    if ($image_file !== null) {
        $sql .= ", image=?";
        $types .= 's';
        $params[] = $image_file;
    }

    $sql .= " WHERE id=?";
    $types .= 'i';
    $params[] = $id;

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param($types, ...$params);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
    }


    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM songs WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param('i', $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public function toggleFavorite($song_id, $user_id) {
        $check = $this->conn->prepare("SELECT 1 FROM favorites WHERE song_id=? AND user_id=? LIMIT 1");
        if (!$check) return false;

        $check->bind_param('ii', $song_id, $user_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            $stmt = $this->conn->prepare("DELETE FROM favorites WHERE song_id=? AND user_id=?");
            if (!$stmt) return false;

            $stmt->bind_param('ii', $song_id, $user_id);
            $stmt->execute();
            $stmt->close();
            return 'removed';
        } else {
            $stmt = $this->conn->prepare("INSERT INTO favorites (song_id, user_id, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) return false;

            $stmt->bind_param('ii', $song_id, $user_id);
            $stmt->execute();
            $stmt->close();
            return 'added';
        }
    }

    public function getAllAdmin() {
        $result = $this->conn->query("
            SELECT s.*, COALESCE(pc.play_count, 0) AS play_count
            FROM songs s
            LEFT JOIN play_counts pc ON s.id = pc.song_id
            ORDER BY s.created_at DESC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    // ====== THỐNG KÊ (STATISTICS) ======

    // Tính tổng lượt nghe toàn hệ thống (từ bảng play_counts)
    public function getTotalPlays(): int {
    $sql = "SELECT COALESCE(SUM(play_count), 0) AS total FROM play_counts";
    $res = $this->conn->query($sql);
    if (!$res) return 0;
    return (int)$res->fetch_assoc()['total'];
}

public function getTotalArtists(): int {
    $sql = "
        SELECT COUNT(DISTINCT TRIM(LOWER(artist))) AS total
        FROM songs
        WHERE artist IS NOT NULL AND artist <> ''
    ";
    $res = $this->conn->query($sql);
    if (!$res) return 0;
    return (int)$res->fetch_assoc()['total'];
    }
}
?>
