<?php
session_start();
include 'config.php';

// ලංකාවේ වෙලාවට සැකසීම
date_default_timezone_set("Asia/Colombo");

if (!isset($_SESSION['user_id'])) exit;

$student_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$recording_id = isset($_POST['recording_id']) ? mysqli_real_escape_string($conn, $_POST['recording_id']) : '';
$now = date("Y-m-d H:i:s");

if ($action == 'start') {
    // වීඩියෝව නැරඹීම ආරම්භ කරන විට
    $stmt = $conn->prepare("INSERT INTO recording_history (student_id, recording_id, start_time, duration_watched) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iis", $student_id, $recording_id, $now);
    $stmt->execute();
    echo $conn->insert_id; 
} 

elseif ($action == 'stop') {
    // වීඩියෝව නැවැත්වූ විට හෝ පේජ් එකෙන් අයින් වූ විට
    $history_id = isset($_POST['history_id']) ? mysqli_real_escape_string($conn, $_POST['history_id']) : '';
    
    if (!empty($history_id)) {
        /**
         * මෙහිදීTIMESTAMPDIFF(SECOND, start_time, '$now') මගින් 
         * පටන් ගත් වෙලාව සහ දැන් වෙලාව අතර වෙනස තත්පර වලින් ලබාගෙන 
         * කෙලින්ම duration_watched එකට ඇතුළත් කරයි.
         */
        $update_sql = "UPDATE recording_history 
                       SET end_time = '$now', 
                           duration_watched = TIMESTAMPDIFF(SECOND, start_time, '$now') 
                       WHERE history_id = '$history_id'";
        
        $conn->query($update_sql);
    }
}
?>