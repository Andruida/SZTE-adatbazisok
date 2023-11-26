<?php
define('IMDB', true);
require_once 'conn.php';
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

$new = empty($_POST['id']);
$actor_id = ($new) ? guidv4() : $_POST['id'];

foreach (["name", "birthday", "country"] as $key) {
    if (!isset($_POST[$key]) || empty($_POST[$key])) {
        http_response_code(400);
        exit;
    }
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birthday'])) {
    http_response_code(400);
    exit;
}

$conn->beginTransaction();
if (!$new) {
    $stmt = $conn->prepare(
        "SELECT `id` FROM `actors` WHERE `id` = :id;"
    );
    $stmt->bindParam(':id', $actor_id);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        exit;
    }
}

$stmt = $conn->prepare(
    "SELECT `id` FROM `countries` WHERE `id` = :id;"
);
$stmt->bindParam(':id', $_POST['country']);
$stmt->execute();
if ($stmt->rowCount() === 0) {
    http_response_code(400);
    exit;
}


if ($new) {
    $stmt = $conn->prepare(
        "INSERT INTO `actors` (`id`, `name`, `birthday`, `country`) VALUES (:id, :name, :birthday, :country);"
    );
    $stmt->bindValue(':id', $actor_id);
} else {
    $stmt = $conn->prepare(
        "UPDATE `actors` SET `name` = :name, `birthday` = :birthday, `country` = :country WHERE `id` = :id;"
    );
    $stmt->bindParam(':id', $actor_id);
}
$stmt->bindParam(':name', $_POST['name']);
$stmt->bindParam(':birthday', $_POST['birthday']);
$stmt->bindParam(':country', $_POST['country']);

$stmt->execute();

$conn->commit();

header('Location: /actor.php?id='.$actor_id);

?>