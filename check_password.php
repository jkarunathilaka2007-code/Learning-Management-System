<?php
session_start();
include 'config.php';

$password = $_POST['password'];
$id = $_SESSION['user_id'];

$user = $conn->query("SELECT password FROM student WHERE id = '$id'")->fetch_assoc();

if (password_verify($password, $user['password'])) {
    echo "match";
} else {
    echo "nomatch";
}
?>