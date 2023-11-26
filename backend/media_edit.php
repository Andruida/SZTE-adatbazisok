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
$movie_mode = isset($_POST['movie']);
$series_mode = isset($_POST['series']);
$movie_id = null;
$series_id = null;
if ($new && !$movie_mode && !$series_mode) {
    http_response_code(400);
    exit;
}

$conn->beginTransaction();

if (!$new) {
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
    unset($media);
}

if ($movie_mode) {
    foreach (["title", "genre", "length", "released"] as $key) {
        if (!isset($_POST[$key]) || empty($_POST[$key])) {
            http_response_code(400);
            exit;
        }
    }
}
if ($series_mode) {
    foreach (["title", "genre", "seasons", "episodes"] as $key) {
        if (!isset($_POST[$key]) || empty($_POST[$key])) {
            http_response_code(400);
            exit;
        }
    }
}

$stmt = $conn->prepare(
    "SELECT `id` FROM `genres` WHERE `name` = :name;"
);
$stmt->bindParam(':name', $_POST['genre']);
$stmt->execute();
$genre = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$genre) {
    $stmt = $conn->prepare(
        "INSERT INTO `genres` (`id`, `name`) VALUES (:id, :name);"
    );
    $genre = [];
    $genre["id"] = guidv4();
    $genre["name"] = $_POST['genre'];
    $stmt->bindParam(':id', $genre['id']);
    $stmt->bindParam(':name', $genre['name']);
    $stmt->execute();
    
}

if ($movie_mode) {
    if ($new) {
        $stmt = $conn->prepare(
            "INSERT INTO `movies` (`id`, `length`, `released`) VALUES (:id, :length, :released);"
        );
        $movie_id = guidv4();
    } else {
        $stmt = $conn->prepare(
            "UPDATE `movies` SET `length` = :length, `released` = :released WHERE `id` = :id;"
        );
    }
    $stmt->bindParam(':id', $movie_id);
    $stmt->bindParam(':length', $_POST['length']);
    $stmt->bindParam(':released', $_POST['released']);
    $stmt->execute();
} else if ($series_mode) {
    if ($new) {
        $stmt = $conn->prepare(
            "INSERT INTO `series` (`id`,`seasons`, `episodes`) VALUES (:id, :seasons, :episodes);"
        );
        $series_id = guidv4();
    } else {
        $stmt = $conn->prepare(
            "UPDATE `series` SET `seasons` = :seasons, `episodes` = :episodes WHERE `id` = :id;"
        );
    }
    $stmt->bindParam(':id', $series_id);
    $stmt->bindParam(':seasons', $_POST['seasons']);
    $stmt->bindParam(':episodes', $_POST['episodes']);
    $stmt->execute();
}

if ($new) {
    $stmt = $conn->prepare(
        "INSERT INTO `media` (`id`, `title`, `genre`, `movie`, `series`) VALUES (:id, :title, :genre, :movie, :series);"
    );
    $media_id = guidv4();
    $stmt->bindParam(':id', $media_id);
    $stmt->bindParam(':title', $_POST['title']);
    $stmt->bindParam(':genre', $genre['id']);
    $stmt->bindParam(':movie', $movie_id);
    $stmt->bindParam(':series', $series_id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare(
        "UPDATE `media` SET `title` = :title, `genre` = :genre, `movie` = :movie, `series` = :series WHERE `id` = :id;"
    );
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->bindParam(':title', $_POST['title']);
    $stmt->bindParam(':genre', $genre['id']);
    $stmt->bindParam(':movie', $movie_id);
    $stmt->bindParam(':series', $series_id);
    $stmt->execute();
    $media_id = $_POST['id'];
}

$stmt = $conn->prepare(
    "DELETE FROM `actor_media` WHERE `media` = :media;"
);
$stmt->bindParam(':media', $media_id);
$stmt->execute();

foreach ($_POST['actors'] as $k => $actor) {
    if (empty($actor) || $actor == -1) {
        continue;
    }
    $stmt = $conn->prepare(
        "SELECT `id` FROM `actors` WHERE `id` = :id;"
    );
    $stmt->bindParam(':id', $actor);
    $stmt->execute();
    $actor_obj = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$actor_obj) {
        continue;
    }
    $actor_id = $actor_obj['id'];

    $stmt = $conn->prepare(
        "INSERT INTO `actor_media` (`actor`, `media`, `role`) VALUES (:actor, :media, :role);"
    );
    $stmt->bindParam(':actor', $actor_id);
    $stmt->bindParam(':media', $media_id);
    $stmt->bindParam(':role', $_POST['roles'][$k]);
    $stmt->execute();
}


$conn->commit();
header("Location: /media_edit.php?id={$media_id}");



?>