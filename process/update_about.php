<?php
session_start();
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Basic Info
    $bio = $conn->real_escape_string($_POST['bio']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $email = $conn->real_escape_string($_POST['email']);
    $address = $conn->real_escape_string($_POST['address']);

    // 1. Handling Images (Desktop, Mobile, Profile)
    $upload_dir = "../assets/img/about/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    function uploadImage($file_key, $dir) {
        if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
            $filename = time() . "_" . $_FILES[$file_key]['name'];
            move_uploaded_file($_FILES[$file_key]['tmp_name'], $dir . $filename);
            return "assets/img/about/" . $filename;
        }
        return null;
    }

    $desktop_banner = uploadImage('desktop_banner', $upload_dir);
    $mobile_banner = uploadImage('mobile_banner', $upload_dir);
    $profile_pic = uploadImage('profile_pic', $upload_dir);

    // 2. Handling Multiple Group Links (Converting to JSON)
    $links_array = [];
    if (isset($_POST['media_type'])) {
        for ($i = 0; $i < count($_POST['media_type']); $i++) {
            if (!empty($_POST['link'][$i])) {
                $links_array[] = [
                    'media' => $_POST['media_type'][$i],
                    'town' => $_POST['town'][$i],
                    'group_name' => $_POST['group_name'][$i],
                    'url' => $_POST['link'][$i]
                ];
            }
        }
    }
    $group_links_json = $conn->real_escape_string(json_encode($links_array));

    // 3. Handling Timetable (Converting to JSON for easy storage)
    $timetable_array = [];
    if (isset($_POST['year'])) {
        for ($j = 0; $j < count($_POST['year']); $j++) {
            if (!empty($_POST['year'][$j])) {
                $timetable_array[] = [
                    'year' => $_POST['year'][$j],
                    'stream' => $_POST['stream'][$j],
                    'subject' => $_POST['subject'][$j],
                    'day' => $_POST['day'][$j],
                    'time' => $_POST['time'][$j]
                ];
            }
        }
    }
    $timetable_json = $conn->real_escape_string(json_encode($timetable_array));

    // 4. Update Database
    // පළමුව record එකක් තියෙනවාද බලමු
    $check = $conn->query("SELECT id FROM about LIMIT 1");
    
    if ($check->num_rows > 0) {
        // පරණ Images තියාගන්න (අලුත් ඒවා upload කරේ නැත්නම්)
        $sql = "UPDATE about SET bio='$bio', contact_number='$contact', email='$email', address='$address', group_links='$group_links_json', timetable='$timetable_json'";
        
        if ($desktop_banner) $sql .= ", desktop_banner='$desktop_banner'";
        if ($mobile_banner) $sql .= ", mobile_banner='$mobile_banner'";
        if ($profile_pic) $sql .= ", profile_pic='$profile_pic'";
        
        $sql .= " WHERE id=1";
    } else {
        // අලුතින්ම දත්ත ඇතුළත් කිරීම
        $sql = "INSERT INTO about (bio, contact_number, email, address, desktop_banner, mobile_banner, profile_pic, group_links, timetable) 
                VALUES ('$bio', '$contact', '$email', '$address', '$desktop_banner', '$mobile_banner', '$profile_pic', '$group_links_json', '$timetable_json')";
    }

    if ($conn->query($sql)) {
        echo "<script>alert('Settings Saved Successfully!'); window.location.href='../about_data.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>