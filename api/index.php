<?php
// api/index.php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';
require_once '../controllers/AuthController.php';
require_once '../controllers/SongController.php';
require_once '../controllers/PlaylistController.php';

include 'config/cors.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));
array_shift($uri); // bỏ "api"

if ($uri[0] !== 'api') {
    http_response_code(404);
    echo json_encode(["error" => "API không tồn tại"]);
    exit;
}
array_shift($uri); // bỏ 'api'

$method = $_SERVER['REQUEST_METHOD'];

switch ($uri[0] ?? '') {
    case 'auth':
        if ($uri[1] === 'login' && $method === 'POST') require 'auth/login.php';
        elseif ($uri[1] === 'register' && $method === 'POST') require 'auth/register.php';
        break;

    case 'songs':
        if ($uri[1] === 'get_all' && $method === 'GET') require 'songs/get_all.php';
        elseif ($uri[1] === 'get_by_id' && $method === 'GET') require 'songs/get_by_id.php';
        elseif ($uri[1] === 'search' && $method === 'GET') require 'songs/search.php';
        elseif ($uri[1] === 'upload' && $method === 'POST') require 'songs/upload.php';
        break;

    case 'user':
        if ($uri[1] === 'profile' && $method === 'GET') require 'user/profile.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint không tồn tại"]);
}