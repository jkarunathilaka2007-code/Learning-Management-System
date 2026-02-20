<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$st = $conn->query("SELECT * FROM student WHERE id = '$student_id'")->fetch_assoc();

$msg = "";
$msg_type = "";

// 1. General Update
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $nic = mysqli_real_escape_string($conn, $_POST['nic_number']);
    $school = mysqli_real_escape_string($conn, $_POST['school']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $town = mysqli_real_escape_string($conn, $_POST['town']);

    $img_name = $st['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $img_name = time() . '_' . $_FILES['profile_pic']['name'];
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], "uploads/profile_pics/" . $img_name);
    }

    $sql = "UPDATE student SET full_name='$full_name', nic_number='$nic', school='$school', 
            dob='$dob', address='$address', contact_number='$contact', town='$town', 
            profile_pic='$img_name' WHERE id='$student_id'";

    if ($conn->query($sql)) {
        $msg = "Profile updated successfully! ✨";
        $msg_type = "success";
        header("Refresh:2");
    } else {
        $msg = "Update failed. Please try again.";
        $msg_type = "error";
    }
}

// 2. Password Update
if (isset($_POST['change_password'])) {
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    if ($conn->query("UPDATE student SET password='$new_pass' WHERE id='$student_id'")) {
        $msg = "Password changed! Redirecting to login...";
        $msg_type = "success";
        echo "<script>setTimeout(() => { window.location.href='login.php'; }, 2500);</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Account Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fdfdfd; -webkit-tap-highlight-color: transparent; }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 2.5rem; }
        .tab-active { background: #0f172a; color: white; transform: scale(1.05); }
        .input-field { background: #f1f5f9; border: 2px solid transparent; transition: 0.3s; }
        .input-field:focus { border-color: #6366f1; background: white; outline: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        
        /* Custom Toast Animation */
        .toast-msg {
            position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
            z-index: 9999; padding: 16px 30px; border-radius: 20px;
            font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 1px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1); animation: slideDown 0.5s ease forwards;
        }
        @keyframes slideDown { from { top: -100px; opacity: 0; } to { top: 20px; opacity: 1; } }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <?php if($msg): ?>
        <div class="toast-msg <?= $msg_type == 'success' ? 'bg-slate-900 text-emerald-400' : 'bg-rose-600 text-white' ?>">
            <i class="fas <?= $msg_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-2"></i>
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="bg-slate-900 pt-10 pb-24 px-6 rounded-b-[3.5rem] shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="max-w-xl mx-auto flex justify-between items-center relative">
            <a href="student_page.php" class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center text-white border border-white/10">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <h1 class="text-white font-black uppercase tracking-widest text-[12px]">Profile Settings</h1>
            <div class="w-10 h-10"></div>
        </div>
    </div>

    <main class="max-w-xl mx-auto px-5 -mt-16 relative z-10 pb-20">
        <div class="glass-card shadow-2xl p-6 md:p-8">
            
            <div class="flex bg-slate-100 p-1.5 rounded-2xl mb-8">
                <button onclick="switchTab('gen')" id="btn-gen" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all tab-active">General</button>
                <button onclick="switchTab('sec')" id="btn-sec" class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 transition-all">Password</button>
            </div>

            <div id="tab-gen" class="space-y-6">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-24 h-24 rounded-[2rem] overflow-hidden border-4 border-white shadow-xl bg-slate-200">
                                <img id="p-img" src="uploads/profile_pics/<?= $st['profile_pic'] ?: 'default.png' ?>" class="w-full h-full object-cover">
                            </div>
                            <label class="absolute -bottom-2 -right-2 bg-slate-900 text-white w-9 h-9 rounded-xl flex items-center justify-center cursor-pointer shadow-lg">
                                <i class="fas fa-camera text-xs"></i>
                                <input type="file" name="profile_pic" class="hidden" onchange="preview(this)">
                            </label>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Full Name</label>
                                <input type="text" name="full_name" value="<?= $st['full_name'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700">
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Gmail (Read Only)</label>
                                <input type="text" value="<?= $st['gmail'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-400 opacity-60" readonly>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">NIC Number</label>
                                <input type="text" name="nic_number" value="<?= $st['nic_number'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700">
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Date of Birth</label>
                                <input type="date" name="dob" value="<?= $st['dob'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700">
                            </div>
                        </div>

                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">School</label>
                            <input type="text" name="school" value="<?= $st['school'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Contact</label>
                                <input type="text" name="contact_number" value="<?= $st['contact_number'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700">
                            </div>
                            <div>
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Town</label>
                                <input type="text" name="town" value="<?= $st['town'] ?>" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700">
                            </div>
                        </div>

                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Home Address</label>
                            <textarea name="address" rows="2" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-slate-700"><?= $st['address'] ?></textarea>
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="w-full bg-slate-900 text-white py-4 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl active:scale-95 transition-all">Update Now</button>
                </form>
            </div>

            <div id="tab-sec" class="hidden py-4 text-center">
                <div id="v-box">
                    <div class="w-16 h-16 bg-slate-100 text-slate-800 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-fingerprint text-2xl"></i>
                    </div>
                    <h3 class="font-black text-slate-800 uppercase text-xs">Verify Password</h3>
                    <p class="text-[10px] text-slate-400 font-bold mb-6 italic">Enter current password to unlock</p>
                    <input type="password" id="c-pass" class="input-field w-full p-4 rounded-2xl font-bold text-sm text-center mb-4" placeholder="••••••••">
                    <button onclick="verify()" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-lg">Verify Identity</button>
                    <p id="err" class="text-rose-500 text-[10px] font-black uppercase mt-4"></p>
                </div>

                <form action="" method="POST" id="n-pass-form" class="hidden space-y-4 text-left">
                    <div class="bg-indigo-50 p-4 rounded-2xl text-center mb-4 border border-indigo-100">
                        <p class="text-indigo-600 text-[9px] font-black uppercase tracking-tighter">Verified! Setup Your New Password</p>
                    </div>
                    <input type="password" name="new_password" id="p1" placeholder="New Password" class="input-field w-full p-4 rounded-2xl font-bold text-sm" required>
                    <input type="password" id="p2" placeholder="Confirm Password" class="input-field w-full p-4 rounded-2xl font-bold text-sm" required>
                    <button type="submit" name="change_password" onclick="return check()" class="w-full bg-rose-600 text-white py-4 rounded-2xl font-black text-[11px] uppercase tracking-widest">Change & Logout</button>
                </form>
            </div>

        </div>
    </main>
    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.2)]">
        <a href="index.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Home</span>
        </a>
        </a>
        <a href="student_page.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-user text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Profile</span>
        </a>
        <a href="logout.php" class="text-rose-500 flex flex-col items-center">
            <i class="fas fa-power-off text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

    <script>
        function switchTab(t) {
            $('.tab-content, #tab-gen, #tab-sec').addClass('hidden');
            $('#tab-'+t).removeClass('hidden');
            $('#btn-gen, #btn-sec').removeClass('tab-active text-white').addClass('text-slate-500');
            $('#btn-'+t).addClass('tab-active text-white').removeClass('text-slate-500');
        }

        function preview(input) {
            if (input.files && input.files[0]) {
                var r = new FileReader();
                r.onload = function(e) { $('#p-img').attr('src', e.target.result); }
                r.readAsDataURL(input.files[0]);
            }
        }

        function verify() {
            let p = $('#c-pass').val();
            $.post('check_password.php', {password: p}, function(res) {
                if(res === 'match') {
                    $('#v-box').fadeOut(300, function() { $('#n-pass-form').fadeIn().removeClass('hidden'); });
                } else {
                    $('#err').text("Invalid Current Password!").hide().fadeIn();
                }
            });
        }

        function check() {
            if($('#p1').val() !== $('#p2').val()) { alert("Passwords do not match!"); return false; }
            return true;
        }

        // Hide toast after 3 seconds
        setTimeout(() => { $('.toast-msg').fadeOut(); }, 3000);
    </script>
</body>
</html>