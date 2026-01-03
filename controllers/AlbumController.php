<?php
class AlbumController {
    private $albumModel;

    public function __construct() {
        require_once '../models/Album.php';
        $this->albumModel = new Album();
    }

    public function getUserAlbums($user_id) {
        return $this->albumModel->getByUserId($user_id);
    }

    public function create($user_id, $name, $cover_image = "default.jpg") {
        return $this->albumModel->create($user_id, $name, $cover_image);
    }

    public function update($album_id, $name) {
        return $this->albumModel->update($album_id, $name);
    }

    public function delete($album_id, $user_id) {
        return $this->albumModel->delete($album_id, $user_id);
    }

    public function addSong($album_id, $song_id) {
        return $this->albumModel->addSong($album_id, $song_id);
    }

    public function removeSong($album_id, $song_id) {
        return $this->albumModel->removeSong($album_id, $song_id);
    }

    public function getAlbumById($album_id, $user_id = null) {
        return $this->albumModel->getById($album_id, $user_id);
    }

    public function getSongsInAlbum($album_id) {
        return $this->albumModel->getSongs($album_id);
    }
    public function countSongs($album_id) {
    return $this->albumModel->countSongs($album_id);
    }
}
?>
