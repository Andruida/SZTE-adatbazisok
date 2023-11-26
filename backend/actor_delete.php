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
    "SELECT `id` FROM `actors` WHERE `id` = :id;"
);
$stmt->bindParam(':id', $_POST['id']);
$stmt->execute();
$actor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$actor) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM `actors` WHERE `id` = :id;"
);
$stmt->bindParam(':id', $_POST['id']);
$stmt->execute();

header('Location: /?deleted');

?>