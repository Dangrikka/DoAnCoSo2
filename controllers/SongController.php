<?php
// controllers/SongController.php

class SongController {
    private $songModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Song.php';
        $this->songModel = new Song();
    }

    // ====== LẤY TẤT CẢ BÀI HÁT ======
    public function getAllSongs() {
        return $this->songModel->getAllSongs();
    }

    // ====== TRANG CHỦ – PHÂN TRANG ======
    public function index($userId = null) {
        $limit = 5;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        return [
            'songs' => $this->songModel->getSongsByPage($userId, $limit, $offset),
            'page' => $page,
            'totalPages' => ceil($this->songModel->countSongs() / $limit)
        ];
    }

    // ====== SEARCH ======
    public function search($keyword, $userId = null) {
        return $this->songModel->search(trim($keyword), $userId);
    }

    // ====== CHI TIẾT BÀI HÁT ======
    public function show($id, $userId = null) {
        return $this->songModel->getById($id, $userId);
    }

    // ====== YÊU THÍCH ======
    public function favorites($userId) {
        return $this->songModel->getFavoriteSongs($userId);
    }

    // ====== BXH ======
    public function charts($userId = null, $limit = 20) {
        return $this->songModel->getTopByPlayCount($userId, $limit);
    }

    // ====== THÊM BÀI HÁT ======
    public function store($title, $artist, $audio_file, $image_file = 'default.jpg') {
        return $this->songModel->create($title, $artist, $audio_file, $image_file);
    }

    // ====== CẬP NHẬT BÀI HÁT ======
    public function update($id, $title, $artist, $audio_file = null, $image_file = null) {
        return $this->songModel->update($id, $title, $artist, $audio_file, $image_file);
    }

    // ====== XÓA ======
    public function delete($id) {
        return $this->songModel->delete($id);
    }

    // ====== FAVORITE ======
    public function toggleFavorite($song_id, $user_id) {
        return $this->songModel->toggleFavorite((int)$song_id, (int)$user_id);
    }

    // ====== PLAY COUNT ======
    public function incrementPlayCount($song_id) {
        return $this->songModel->incrementPlayCount((int)$song_id);
    }

    // ====== ADMIN ======
    public function getAllAdmin() {
        return $this->songModel->getAllAdmin();
    }

    // ====== LẤY BÀI HÁT CHO EDIT ======
    public function getSongById($id, $userId = null) {
        $song = $this->songModel->getById($id, $userId);
        if (!$song) return false;

        // Chuẩn hóa cho View
        $song['file_path'] = $song['audio_file'];
        $song['image'] = $song['image'] ?? 'default.jpg';

        return $song;
    }

    // ====== THỐNG KÊ ======
    public function getStatistics() {
        return [
            'totalSongs'   => $this->songModel->countSongs(),
            'totalPlays'   => $this->songModel->getTotalPlays(),
            'totalArtists' => $this->songModel->getTotalArtists(),
            'topSongs'     => $this->songModel->getTopByPlayCount(null, 5)
        ];
    }
}
