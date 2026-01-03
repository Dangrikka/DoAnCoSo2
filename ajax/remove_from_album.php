<?php
session_start();
header("Content-Type: application/json");

require_once "../config/database.php";
require_once "../controllers/AlbumController.php";

$albumCtrl = new AlbumController();

$album_id = intval($_POST['album_id']);
$song_id  = intval($_POST['song_id']);

if ($albumCtrl->removeSong($album_id, $song_id)) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
