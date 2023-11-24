<?php
define('IMDB', true);

require_once 'utils.php';
require_once 'conn.php';

session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['password2']) || !isset($_POST['fullname'])) {
    http_response_code(400);
    exit;
}

if ($_POST['password'] !== $_POST['password2']) {
    header('Location: /register.php?error=no_match');
    exit;
}

$id = guidv4();
$username = $_POST['username'];
$password = $_POST['password'];
$fullname = $_POST['fullname'];

$stmt = $conn->prepare('SELECT `id` FROM `users` WHERE `username` = :username');
$stmt->bindParam(':username', $username);
$stmt->execute();
if ($stmt->rowCount() > 0) {
    header('Location: /register.php?error=username_taken');
    exit;
}

$stmt = $conn->prepare('INSERT INTO `users` (`id`, `username`, `password`, `name`) VALUES (:id, :username, :pw, :fullname)');
$stmt->bindParam(':id', $id);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':pw', password_hash($password, PASSWORD_BCRYPT));
$stmt->bindParam(':fullname', $fullname);
$stmt->execute();

$_SESSION['user_id'] = $id;
$_SESSION['user_name'] = $username;
$_SESSION['user_fullname'] = $fullname;

header('Location: /');

?>