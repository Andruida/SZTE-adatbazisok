<?php
if (!defined('IMDB')) {
    http_response_code(403);
    exit;
}

$_servername = "db";
$_username = "imdb";
$_password = "hackme";
$_database = "imdb";

$conn = new PDO("mysql:host=$_servername;dbname=$_database", $_username, $_password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

unset($_servername, $_username, $_password, $_database);
?>