<?php
session_start();
include 'config.php';

if (isset($_POST['paper_id']) && isset($_POST['marks'])) {
    $paper_id = mysqli_real_escape_string($conn, $_POST['paper_id']);
    $max_marks = floatval($_POST['max_marks']);
    $marks_array = $_POST['marks'];

    foreach ($marks_array as $student_id => $raw_mark) {
        if ($raw_mark !== "") {
            $student_id = mysqli_real_escape_string($conn, $student_id);
            $obtained_mark = floatval($raw_mark);

            // Absent නම් (අපි -1 ලෙස එවනවා) % එක 0 කරයි
            $percentage = ($obtained_mark <= 0) ? 0 : ($obtained_mark / $max_marks) * 100;

            $sql = "INSERT INTO student_marks (paper_id, student_id, marks_obtained, percentage) 
                    VALUES ('$paper_id', '$student_id', '$obtained_mark', '$percentage')
                    ON DUPLICATE KEY UPDATE marks_obtained = '$obtained_mark', percentage = '$percentage'";
            $conn->query($sql);
        }
    }
    echo "success";
}
?>