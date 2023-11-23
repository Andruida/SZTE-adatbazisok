<?php
$_servername = "db";
$_username = "imdb";
$_password = "hackme";
$_database = "imdb";

$conn = new mysqli($_servername, $_username, $_password, $_database);

register_shutdown_function("mysqli_close", $conn);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

unset($_servername, $_username, $_password, $_database);
?>