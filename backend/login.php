<?php
define('IMDB', true);

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

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    http_response_code(400);
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare('SELECT `id`, `username`, `password`, `name` FROM `users` WHERE `username` = :username');
$stmt->bindParam(':username', $username);
$stmt->execute();
if ($stmt->rowCount() === 0) {
    header('Location: /login.php?error=invalid');
    exit;
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!password_verify($password, $user['password'])) {
    header('Location: /login.php?error=invalid');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['username'];
$_SESSION['user_fullname'] = $user['name'];

header('Location: /');
?>