<?php
session_start();
include '../config.php'; // config file එකට නිවැරදි path එක දෙන්න

if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $file_source = mysqli_real_escape_string($conn, $_POST['file_source']);
    
    $final_path = "";

    if ($file_source === 'upload') {
        // File Upload Logic
        if (isset($_FILES['paper_file']) && $_FILES['paper_file']['error'] === 0) {
            $file_name = $_FILES['paper_file']['name'];
            $file_tmp = $_FILES['paper_file']['tmp_name'];
            
            // File extension එක check කිරීම
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if ($file_ext === 'pdf') {
                // අලුත් නමක් දීම (e.g., paper_17000000.pdf)
                $new_file_name = "paper_" . time() . "_" . rand(1000, 9999) . ".pdf";
                $upload_path = "../uploads/past_papers/" . $new_file_name;

                // Folder එක නැත්නම් හදන්න
                if (!is_dir('../uploads/past_papers/')) {
                    mkdir('../uploads/past_papers/', 0777, true);
                }

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $final_path = $new_file_name;
                } else {
                    header("Location: ../add_p_papers.php?status=error&msg=upload_failed");
                    exit();
                }
            } else {
                header("Location: ../add_p_papers.php?status=error&msg=invalid_format");
                exit();
            }
        }
    } else {
        // External Link Logic
        $final_path = mysqli_real_escape_string($conn, $_POST['file_url']);
    }

    // Database Insert
    if (!empty($final_path)) {
        $sql = "INSERT INTO past_papers (title, year, category, file_source, paper_file) 
                VALUES ('$title', '$year', '$category', '$file_source', '$final_path')";

        if ($conn->query($sql)) {
            header("Location: ../add_p_papers.php?status=success");
        } else {
            header("Location: ../add_p_papers.php?status=error");
        }
    } else {
        header("Location: ../add_p_papers.php?status=error&msg=empty_file");
    }
} else {
    header("Location: ../add_p_papers.php");
}
?>