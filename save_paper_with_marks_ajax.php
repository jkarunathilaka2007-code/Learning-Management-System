<?php
session_start();
include 'config.php';

if (isset($_POST['class_id']) && isset($_POST['marks'])) {
    
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $paper_name = mysqli_real_escape_string($conn, $_POST['paper_name']);
    $max_marks = floatval($_POST['max_marks']);
    $marks_array = $_POST['marks']; // මෙය array එකක් (student_id => marks)

    // 1. මුලින්ම පේපර් එක 'exam_papers' table එකට ඇතුළත් කරනවා
    $insert_paper = $conn->query("INSERT INTO exam_papers (class_id, paper_name, max_marks) 
                                  VALUES ('$class_id', '$paper_name', '$max_marks')");

    if ($insert_paper) {
        $paper_id = $conn->insert_id; // අලුතින් හැදුණු පේපර් එකේ ID එක
        $success_count = 0;

        // 2. එක් එක් ශිෂ්‍යයාගේ ලකුණු Loop එකක් හරහා Save කරනවා
        foreach ($marks_array as $student_id => $raw_mark) {
            
            // ලකුණු ඇතුළත් කර ඇත්නම් පමණක් (හිස් ඒවා ignore කරයි)
            if ($raw_mark !== "") {
                $student_id = mysqli_real_escape_string($conn, $student_id);
                $obtained_mark = floatval($raw_mark);

                // ප්‍රතිශතය ගණනය කිරීම: (ලබාගත් ලකුණු / උපරිම ලකුණු) * 100
                $percentage = ($obtained_mark / $max_marks) * 100;

                $sql = "INSERT INTO student_marks (paper_id, student_id, marks_obtained, percentage) 
                        VALUES ('$paper_id', '$student_id', '$obtained_mark', '$percentage')
                        ON DUPLICATE KEY UPDATE marks_obtained = '$obtained_mark', percentage = '$percentage'";
                
                if ($conn->query($sql)) {
                    $success_count++;
                }
            }
        }

        if ($success_count > 0) {
            echo "success";
        } else {
            echo "Paper created, but no marks were entered.";
        }
    } else {
        echo "Error creating paper: " . $conn->error;
    }
} else {
    echo "Invalid data received.";
}
?>