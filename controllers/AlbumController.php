<?php
// controllers/AlbumController.php
// HOÀN HẢO TUYỆT ĐỐI 2025 – CHUYỂN TỪ PLAYLIST SANG ALBUM

class AlbumController {
    private $albumModel;

    public function __construct() {
        require_once '../models/Album.php';
        $this->albumModel = new Album();
    }

    // LẤY TẤT CẢ ALBUM CỦA USER
    public function getUserAlbums($user_id) {
        return $this->albumModel->getByUserId($user_id);
    }

    // TẠO ALBUM MỚI
    public function create($user_id, $name) {
        $name = trim($name);
        if (empty($name)) return false;
        return $this->albumModel->create($user_id, $name);
    }

    // CẬP NHẬT TÊN ALBUM
    public function update($album_id, $name) {
        $name = trim($name);
        if (empty($name)) return false;
        return $this->albumModel->update($album_id, $name);
    }

    // XÓA ALBUM (chỉ chủ sở hữu)
    public function delete($album_id, $user_id) {
        return $this->albumModel->delete($album_id, $user_id);
    }

    // THÊM BÀI HÁT VÀO ALBUM
    public function addSong($album_id, $song_id) {
        if ($this->albumModel->songExistsInAlbum($album_id, $song_id)) {
            return false; // Tránh trùng
        }
        return $this->albumModel->addSong($album_id, $song_id);
    }

    // XÓA BÀI HÁT KHỎI ALBUM
    public function removeSong($album_id, $song_id) {
        return $this->albumModel->removeSong($album_id, $song_id);
    }

    // LẤY THÔNG TIN 1 ALBUM (có kiểm tra quyền)
    public function getAlbumById($album_id, $user_id = null) {
        return $this->albumModel->getById($album_id, $user_id);
    }

    // LẤY DANH SÁCH BÀI HÁT TRONG ALBUM
    public function getSongsInAlbum($album_id) {
        return $this->albumModel->getSongs($album_id) ?: [];
    }

    // ĐẾM SỐ BÀI HÁT TRONG ALBUM
    public function countSongs($album_id) {
        return $this->albumModel->countSongs($album_id);
    }

    // LẤY ẢNH BÌA ALBUM (ảnh bài hát đầu tiên)
    public function getAlbumCover($album_id) {
        return $this->albumModel->getCover($album_id);
    }
}
?>