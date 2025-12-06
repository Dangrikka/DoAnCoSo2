<?php

if (!isset($GLOBALS['db_connected'])) {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'doancoso2'; 

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Kết nối CSDL thất bại: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    $GLOBALS['db_connected'] = true;
    $GLOBALS['db_conn'] = $conn;
} else {
    $conn = $GLOBALS['db_conn'];
}
?>