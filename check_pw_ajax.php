<?php
session_start();
include 'config.php';

$teacher_id = $_SESSION['user_id'];
$response = [];

// Step 1: Current Password Check
if(isset($_POST['current_pw'])) {
    $current_pw = $_POST['current_pw'];
    $res = $conn->query("SELECT password FROM teacher WHERE id = '$teacher_id'");
    $row = $res->fetch_assoc();

    if(password_verify($current_pw, $row['password'])) {
        $response = ['status' => 'success', 'message' => 'Password Matched! Enter new password below.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Incorrect current password!'];
    }
}

// Step 2: Update New Password
if(isset($_POST['new_pw'])) {
    $new_pw = password_hash($_POST['new_pw'], PASSWORD_DEFAULT);
    $conn->query("UPDATE teacher SET password = '$new_pw' WHERE id = '$teacher_id'");
    $response = ['status' => 'updated'];
}

echo json_encode($response);
?>