<?php
session_start(); // දැනට තියෙන session එක අල්ලගන්න

// සියලුම session variables ඉවත් කරන්න
session_unset();

// session එක සම්පූර්ණයෙන්ම විනාශ කරන්න
session_destroy();

// නැවත login page එකට යොමු කරන්න
header("Location: index.php");
exit();
?>