<?php
// controllers/SongController.php
class SongController {
    private $songModel;

    public function __construct() {
        require_once __DIR__ . '/../models/Song.php';
        $this->songModel = new Song();
    }

    // TRANG CHỦ
    public function index($userId = null) {
        return $this->songModel->getAllWithFavorites($userId);
    }

    // TÌM KIẾM – HÀM CHÍNH (search.php dùng hàm này)
    public function searchSongs($keyword, $userId = null) {
        $keyword = trim($keyword);
        if ($keyword === '') return [];
        return $this->songModel->search($keyword, $userId);
    }

    // Giữ lại để tương thích cũ (nếu có trang nào còn dùng)
    public function search($keyword, $userId = null) {
        return $this->searchSongs($keyword, $userId);
    }

    // CHI TIẾT BÀI HÁT
    public function show($id, $userId = null) {
        return $this->songModel->getById($id, $userId);
    }

    // TRANG YÊU THÍCH
    public function favorites($userId) {
        return $this->songModel->getFavoriteSongs($userId);
    }

    // BẢNG XẾP HẠNG – ĐÃ CHUYỂN SANG LƯỢT NGHE (SIÊU HOT)
    public function charts($userId = null, $limit = 20) {
        return $this->songModel->getTopByPlayCount($userId, $limit);
    }

    // ADMIN: THÊM BÀI HÁT
    public function store($title, $artist, $audio_file, $image_file = 'default.jpg') {
        return $this->songModel->create($title, $artist, $audio_file, $image_file);
    }

    // ADMIN: CẬP NHẬT BÀI HÁT
    public function update($id, $title, $artist, $audio_file = null, $image_file = null) {
        return $this->songModel->update($id, $title, $artist, $audio_file, $image_file);
    }

    // ADMIN: XÓA BÀI HÁT
    public function delete($id) {
        return $this->songModel->delete($id);
    }

    // AJAX: THÊM/XÓA YÊU THÍCH (NÚT TRÁI TIM)
    public function toggleFavorite($song_id, $user_id) {
        return $this->songModel->toggleFavorite((int)$song_id, (int)$user_id);
    }

    // MỚI: TĂNG LƯỢT NGHE KHI PHÁT BÀI HÁT (GỌI TỪ AJAX)
    public function incrementPlayCount($song_id) {
        return $this->songModel->incrementPlayCount((int)$song_id);
    }

    // MỚI: LẤY TOP BÀI HÁT THEO LƯỢT NGHE (dùng cho charts.php)
    public function getTopPlayed($userId = null, $limit = 20) {
        return $this->songModel->getTopByPlayCount($userId, $limit);
    }

    // THÊM MỚI – DUY NHẤT HÀM BẠN ĐANG THIẾU ĐỂ ADMIN/Songs.php HOẠT ĐỘNG
    public function getAllAdmin() {
        return $this->songModel->getAllAdmin();
    }
}
?>