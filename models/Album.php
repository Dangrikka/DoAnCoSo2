<?php
class Album {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getByUserId($user_id) {
        $sql = "SELECT * FROM albums WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($album_id, $user_id = null) {
        $sql = "SELECT * FROM albums WHERE id = ?";
        if ($user_id !== null) $sql .= " AND user_id = ?";

        $stmt = $this->conn->prepare($sql);

        if ($user_id !== null)
            $stmt->bind_param("ii", $album_id, $user_id);
        else
            $stmt->bind_param("i", $album_id);

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($user_id, $name, $cover_image = "default.jpg") {
        $sql = "INSERT INTO albums (user_id, name, cover_image) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $user_id, $name, $cover_image);
        return $stmt->execute() ? $stmt->insert_id : false;
    }

    public function update($album_id, $name) {
        $sql = "UPDATE albums SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $album_id);
        return $stmt->execute();
    }

    public function delete($album_id, $user_id) {
        $sql = "DELETE FROM albums WHERE id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $user_id);
        return $stmt->execute();
    }

    public function songExistsInAlbum($album_id, $song_id) {
        $sql = "SELECT 1 FROM album_songs WHERE album_id = ? AND song_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $song_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function addSong($album_id, $song_id) {
        if ($this->songExistsInAlbum($album_id, $song_id)) return false;

        $sql = "INSERT INTO album_songs (album_id, song_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $song_id);
        return $stmt->execute();
    }

    public function removeSong($album_id, $song_id) {
        $sql = "DELETE FROM album_songs WHERE album_id = ? AND song_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $album_id, $song_id);
        return $stmt->execute();
    }

    public function getSongs($album_id) {
        $sql = "
            SELECT s.*, als.created_at 
            FROM songs s
            JOIN album_songs als ON s.id = als.song_id
            WHERE als.album_id = ?
            ORDER BY als.created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            die("SQL ERROR (getSongs): " . $this->conn->error);
        }

        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countSongs($album_id) {
        $sql = "SELECT COUNT(*) AS total FROM album_songs WHERE album_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $album_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
}
?>
