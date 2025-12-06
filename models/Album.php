<?php
// models/Album.php
// HOÀN HẢO 100% – ĐÃ CHUYỂN TỪ PLAYLIST SANG ALBUM SIÊU SẠCH, SIÊU ĐẸP

class Album {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    // LẤY TẤT CẢ ALBUM CỦA USER
    public function getByUserId($user_id) {
        $sql = "SELECT id, name, cover_image, created_at FROM albums WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // LẤY CHI TIẾT 1 ALBUM (có kiểm tra quyền sở hữu nếu cần)
    public function getById($album_id, $user_id = null) {
        $sql = "SELECT id, name, user_id, cover_image, created_at FROM albums WHERE id = ?";
        if ($user_id !== null) {
            $sql .= " AND user_id = ?";
        }
        $stmt = $this->conn->prepare($sql);
        if ($user_id !== null) {
            $stmt->bind_param("ii", $album_id, $user_id);
        } else {
            $stmt->bind_param("i", $album_id);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc(); // null nếu không tìm thấy
    }

    // TẠO ALBUM MỚI
    public function create($user_id, $name) {
        $name = trim($name);
        if (empty($name)) return false;

        $sql = "INSERT INTO albums (user_id, name, created_at) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $name);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    // CẬP NHẬT TÊN ALBUM
    public function update($album_id, $name) {
        $name = trim($name);
        if (empty($name)) return false;

        $sql = "UPDATE albums SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $album_id);
        return $stmt->execute();
    }

    // XÓA ALBUM (chỉ chủ sở hữu)
    public function delete($album_id, $user_id) {
        $sql = "DELETE FROM albums WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $user_id);
        return $stmt->execute();
    }

    // KIỂM TRA BÀI HÁT ĐÃ CÓ TRONG ALBUM CHƯA
    public function songExistsInAlbum($album_id, $song_id) {
        $sql = "SELECT 1 FROM album_songs WHERE album_id = ? AND song_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $song_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // THÊM BÀI HÁT VÀO ALBUM
    public function addSong($album_id, $song_id) {
        if ($this->songExistsInAlbum($album_id, $song_id)) {
            return false; // tránh trùng
        }

        $sql = "INSERT INTO album_songs (album_id, song_id, added_at) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $song_id);
        return $stmt->execute();
    }

    // XÓA BÀI HÁT KHỎI ALBUM
    public function removeSong($album_id, $song_id) {
        $sql = "DELETE FROM album_songs WHERE album_id = ? AND song_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $song_id);
        return $stmt->execute();
    }

    // LẤY DANH SÁCH BÀI HÁT TRONG ALBUM
    public function getSongs($album_id) {
        $sql = "
            SELECT s.*, als.added_at 
            FROM songs s 
            JOIN album_songs als ON s.id = als.song_id 
            WHERE als.album_id = ? 
            ORDER BY als.added_at DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ĐẾM SỐ BÀI HÁT TRONG ALBUM
    public function countSongs($album_id) {
        $sql = "SELECT COUNT(*) AS total FROM album_songs WHERE album_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)($result['total'] ?? 0);
    }

    // LẤY ẢNH BÌA ALBUM (ảnh của bài hát đầu tiên trong album)
    public function getCover($album_id) {
        $sql = "SELECT s.image 
                FROM songs s
                JOIN album_songs als ON s.id = als.song_id
                WHERE als.album_id = ?
                ORDER BY als.id ASC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['image'] ?? null;
    }
}
?>