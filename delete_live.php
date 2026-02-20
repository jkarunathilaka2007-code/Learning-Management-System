<?php
session_start();
include 'config.php';

// User login වෙලාද කියලා බලනවා
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// URL එකෙන් ID එකක් එනවාද කියලා බලනවා
if (isset($_GET['id'])) {
    $live_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Security check: අදාළ ගුරුවරයාටම අයිති record එකක්ද කියලා බලලා delete කරනවා
    $sql = "DELETE FROM live_classes WHERE id = '$live_id' AND teacher_id = '$teacher_id'";

    if (mysqli_query($conn, $sql)) {
        // සාර්ථක නම් ආපහු live page එකට යවනවා success message එකක් එක්ක
        header("Location: live.php?msg=deleted");
    } else {
        // වැරදීමක් වුණොත් error message එකක් එක්ක යවනවා
        header("Location: live.php?msg=error");
    }
} else {
    // ID එකක් නැතිව ආවොත් direct පිටුපසට යවනවා
    header("Location: live.php");
}
exit();
?>