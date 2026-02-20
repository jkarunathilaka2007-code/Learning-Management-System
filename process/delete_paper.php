<?php
session_start();
include '../config.php';

// Teacher ලොගින් එක check කරන්න (අවශ්‍ය නම්)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. මුලින්ම පරීක්ෂා කරනවා මේක Upload කරපු file එකක්ද කියලා
    $check_sql = "SELECT file_source, paper_file FROM past_papers WHERE id = '$id'";
    $result = $conn->query($check_sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 2. ඒක upload කරපු file එකක් නම් server එකෙන් අයින් කරනවා
        if ($row['file_source'] == 'upload') {
            $file_path = "../uploads/past_papers/" . $row['paper_file'];
            if (file_exists($file_path)) {
                unlink($file_path); // File එක server එකෙන් delete කිරීම
            }
        }

        // 3. Database එකෙන් record එක delete කිරීම
        $delete_sql = "DELETE FROM past_papers WHERE id = '$id'";
        if ($conn->query($delete_sql)) {
            // සාර්ථකව delete වුණාම ආපහු list එකට යනවා
            header("Location: ../p_papers.php?status=deleted");
        } else {
            header("Location: ../p_papers.php?status=error");
        }
    } else {
        header("Location: ../p_papers.php?status=notfound");
    }
} else {
    header("Location: ../p_papers.php");
}
?>