<?php
define('IMDB', true);
require_once 'conn.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['rating'])) {
    http_response_code(400);
    exit;
}

$stmt = $conn->prepare(
    "SELECT `id` FROM `media` WHERE `id` = :id;"
);
$stmt->bindParam(':id', $_POST['id']);
$stmt->execute();
$media = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$media) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO `ratings` (`user`, `media`, `rating`) VALUES (:user, :media, :rating) ON DUPLICATE KEY UPDATE `rating` = :rating;"
);
$stmt->bindParam(':user', $_SESSION['user_id']);
$stmt->bindParam(':media', $_POST['id']);
$stmt->bindParam(':rating', $_POST['rating']);
$stmt->execute();

header('Location: /media.php?id=' . $_POST['id'] . '');

?>