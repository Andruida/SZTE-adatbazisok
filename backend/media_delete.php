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

if (!isset($_POST['id'])) {
    http_response_code(400);
    exit;
}

$stmt = $conn->prepare(
    "SELECT `movie`, `series` FROM `media` WHERE `id` = :id;"
);
$stmt->bindParam(':id', $_POST['id']);
$stmt->execute();
$media = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$media) {
    http_response_code(404);
    exit;
}
$movie_mode = $media['movie'] !== null;
$series_mode = $media['series'] !== null;
$movie_id = $media['movie'] ?? null;
$series_id = $media['series'] ?? null;

if ($movie_mode) {
    $stmt = $conn->prepare(
        "DELETE FROM `movies` WHERE `id` = :id;"
    );
    $stmt->bindParam(':id', $movie_id);
    $stmt->execute();
}
if ($series_mode) {
    $stmt = $conn->prepare(
        "DELETE FROM `series` WHERE `id` = :id;"
    );
    $stmt->bindParam(':id', $series_id);
    $stmt->execute();
}

header('Location: /?deleted');

?>