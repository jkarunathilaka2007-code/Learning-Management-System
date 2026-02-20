<?php
session_start();
include 'config.php';

if(isset($_POST['student_id']) && isset($_POST['class_id'])) {
    
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    $cid = mysqli_real_escape_string($conn, $_POST['class_id']);
    $today = date('Y-m-d');

    // දැනටමත් අද දිනට පැමිණීම සටහන් කර ඇත්දැයි බලමු
    $check = $conn->query("SELECT id FROM attendance WHERE student_id = '$sid' AND class_id = '$cid' AND date = '$today'");

    if($check->num_rows == 0) {
        // 'time' column එක නැති නිසා ඒක අයින් කරලා 'created_at' වලට current timestamp එක වැටෙන්න ඉඩ හරිනවා
        // එහෙම නැත්නම් status සහ date විතරක් ඇතුළත් කරනවා
        $sql = "INSERT INTO attendance (student_id, class_id, status, date) 
                VALUES ('$sid', '$cid', 'present', '$today')";
        
        if($conn->query($sql)) {
            echo "success";
        } else {
            echo "Server Error: " . $conn->error;
        }
    } else {
        echo "exists";
    }
} else {
    echo "invalid_request";
}
?>