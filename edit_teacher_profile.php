<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// --- 1. දත්ත ලබා ගැනීම ---
$teacher_res = $conn->query("SELECT * FROM teacher WHERE id = '$teacher_id'");
$teacher_data = $teacher_res->fetch_assoc();

// --- 2. Profile Update (Tab 1) ---
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $nic = mysqli_real_escape_string($conn, $_POST['nic_number']);
    $profile_pic = $teacher_data['profile_pic'];

    if ($_FILES['profile_image']['name']) {
        $target_dir = "uploads/profile_pics/";
        $file_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $file_name)) {
            // පරණ පින්තූරය Folder එකෙන් මකා දැමීම
            if (!empty($profile_pic) && file_exists($target_dir . $profile_pic) && $profile_pic !== 'default_user.png') {
                unlink($target_dir . $profile_pic);
            }
            $profile_pic = $file_name;
        }
    }

    $update_sql = "UPDATE teacher SET name='$name', contact_number='$contact', nic_number='$nic', profile_pic='$profile_pic' WHERE id='$teacher_id'";
    if ($conn->query($update_sql)) {
        header("Location: edit_teacher_profile.php?s=1&tab=personal");
        exit();
    }
}

// --- 3. Add Class (Tab 3) ---
if (isset($_POST['add_class'])) {
    $year = mysqli_real_escape_string($conn, $_POST['exam_year']);
    $stream = mysqli_real_escape_string($conn, $_POST['stream']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $conn->query("INSERT INTO classes (teacher_id, exam_year, stream, subject) VALUES ('$teacher_id', '$year', '$stream', '$subject')");
    header("Location: edit_teacher_profile.php?s=3&tab=classes_tab");
    exit();
}

// --- 4. Add Town (Tab 4) ---
if (isset($_POST['add_town'])) {
    $town = mysqli_real_escape_string($conn, $_POST['town']);
    $inst = mysqli_real_escape_string($conn, $_POST['institute_name']);
    $conn->query("INSERT INTO class_towns (teacher_id, town, institute_name) VALUES ('$teacher_id', '$town', '$inst')");
    header("Location: edit_teacher_profile.php?s=4&tab=towns_tab");
    exit();
}

// --- 5. Delete Class ---
if (isset($_GET['delete_class'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_class']);
    // මෙතන class_id එකෙන් delete වෙන විදිහට හැදුවා
    $conn->query("DELETE FROM classes WHERE class_id = '$del_id' AND teacher_id = '$teacher_id'");
    header("Location: edit_teacher_profile.php?s=deleted_class&tab=classes_tab");
    exit();
}

// --- 6. Delete Town ---
if (isset($_GET['delete_town'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_town']);
    // (Note: class_towns table එකේ id column එකේ නම town_id නම්, මෙතන id වෙනුවට town_id දෙන්න)
    $conn->query("DELETE FROM class_towns WHERE id = '$del_id' AND teacher_id = '$teacher_id'");
    header("Location: edit_teacher_profile.php?s=deleted_town&tab=towns_tab");
    exit();
}

// --- 7. Update Class Fees (New Tab) ---
if (isset($_POST['update_fees'])) {
    if (isset($_POST['fees'])) {
        foreach ($_POST['fees'] as $class_id => $fee_data) {
            $amount = mysqli_real_escape_string($conn, $fee_data['amount']);
            $type = mysqli_real_escape_string($conn, $fee_data['type']);
            
            // Database එකට update කරනවා
            $conn->query("UPDATE classes SET class_fee = '$amount', fee_type = '$type' WHERE class_id = '$class_id' AND teacher_id = '$teacher_id'");
        }
    }
    header("Location: edit_teacher_profile.php?s=fees_updated&tab=fees_tab");
    exit();
}

// URL messages
if(isset($_GET['s'])) {
    if($_GET['s'] == '1') $success_msg = "Profile Updated Successfully!";
    if($_GET['s'] == '3') $success_msg = "New Class Added!";
    if($_GET['s'] == '4') $success_msg = "Teaching Location Added!";
    if($_GET['s'] == 'deleted_class') $success_msg = "Class Deleted Successfully!";
    if($_GET['s'] == 'deleted_town') $success_msg = "Location Deleted Successfully!";
    if($_GET['s'] == 'fees_updated') $success_msg = "Class Fees Updated Successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        .glass-header {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .tab-btn { 
            transition: 0.3s; border-bottom: 3px solid transparent; 
            color: #94a3b8; font-weight: 700; padding-bottom: 12px; cursor: pointer;
        }
        .tab-btn.active { color: #4f46e5; border-bottom-color: #4f46e5; }
        .tab-content { display: none; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .tab-content.active { display: block; }
        
        @keyframes slideUp { 
            from { opacity: 0; transform: translateY(15px); } 
            to { opacity: 1; transform: translateY(0); } 
        }

        .form-input { 
            width: 100%; padding: 15px 18px; border-radius: 16px; 
            border: 1px solid rgba(0,0,0,0.05); outline: none; transition: 0.3s; 
            background: rgba(255,255,255,0.8); font-size: 14px;
        }
        .form-input:focus { 
            background: white; border-color: #6366f1; 
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.15); 
        }

        @media (min-width: 1024px) {
            .bottom-nav { display: none !important; }
        }
        .bottom-nav { 
            position: fixed; bottom: 0; left: 0; width: 100%; height: 70px; 
            background: #020617; display: flex; justify-content: space-around; 
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-32 antialiased text-slate-900">

    <header class="glass-header sticky top-0 z-50">
        <div class="p-5 flex items-center justify-between max-w-3xl mx-auto">
            <a href="admin_page.php" class="p-2.5 bg-white text-indigo-600 rounded-xl shadow-sm hover:shadow-md transition active:scale-95 border border-slate-100">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="font-black text-slate-800 tracking-tighter italic text-lg">TEACHER SETTINGS</span>
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg shadow-indigo-600/30 overflow-hidden border-2 border-white">
                <img src="uploads/profile_pics/<?= $teacher_data['profile_pic'] ?: 'default_user.png' ?>" class="w-full h-full object-cover">
            </div>
        </div>

        <div class="flex overflow-x-auto no-scrollbar max-w-3xl mx-auto px-5 gap-6 scroll-smooth mt-2">
            <button onclick="openTab(event, 'personal')" id="btn_personal" class="tab-btn active whitespace-nowrap text-[11px] uppercase tracking-widest">Personal</button>
            <button onclick="openTab(event, 'password_tab')" id="btn_password_tab" class="tab-btn whitespace-nowrap text-[11px] uppercase tracking-widest">Security</button>
            <button onclick="openTab(event, 'classes_tab')" id="btn_classes_tab" class="tab-btn whitespace-nowrap text-[11px] uppercase tracking-widest">Classes</button>
            <button onclick="openTab(event, 'fees_tab')" id="btn_fees_tab" class="tab-btn whitespace-nowrap text-[11px] uppercase tracking-widest">Fees</button>
            <button onclick="openTab(event, 'towns_tab')" id="btn_towns_tab" class="tab-btn whitespace-nowrap text-[11px] uppercase tracking-widest">Locations</button>
            <button onclick="window.location.href='about_data.php'" id="btn_fees_tab" class="tab-btn whitespace-nowrap text-[11px] uppercase tracking-widest">About</button>
        </div>
    </header>

    <main class="p-5 lg:p-10 max-w-3xl mx-auto">

        <?php if($success_msg): ?>
            <div class="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 p-4 rounded-2xl mb-6 font-bold text-center backdrop-blur-md animate-pulse">
                <i class="fas fa-check-circle mr-2"></i><?= $success_msg ?>
            </div>
        <?php endif; ?>

        <div id="personal" class="tab-content active space-y-6">
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="glass-card p-8 text-center relative">
                    <div class="relative w-36 h-36 mx-auto mb-5">
                        <img id="preview" src="uploads/profile_pics/<?= $teacher_data['profile_pic'] ?: 'default_user.png' ?>" class="w-full h-full object-cover rounded-full border-[5px] border-white shadow-2xl">
                        <label class="absolute bottom-1 right-1 bg-indigo-600 text-white p-3 rounded-full cursor-pointer hover:scale-110 transition shadow-xl shadow-indigo-600/40">
                            <i class="fas fa-camera text-sm"></i>
                            <input type="file" name="profile_image" accept="image/*" class="hidden" onchange="document.getElementById('preview').src = window.URL.createObjectURL(this.files[0])">
                        </label>
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight italic">Profile Details</h2>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Update your public identity</p>
                </div>

                <div class="glass-card p-8 space-y-5">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 mb-2 block">Full Name</label>
                        <input type="text" name="name" class="form-input" value="<?= $teacher_data['name'] ?>" required>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 mb-2 block">Gmail Address (Read-only)</label>
                        <input type="email" class="form-input bg-slate-100 text-slate-500 cursor-not-allowed border-none" value="<?= isset($teacher_data['gmail']) ? $teacher_data['gmail'] : (isset($teacher_data['email']) ? $teacher_data['email'] : '') ?>" readonly>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 mb-2 block">Contact Number</label>
                            <input type="text" name="contact_number" class="form-input" value="<?= $teacher_data['contact_number'] ?>">
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 mb-2 block">NIC Number</label>
                            <input type="text" name="nic_number" class="form-input" value="<?= isset($teacher_data['nic_number']) ? $teacher_data['nic_number'] : '' ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-[0.2em] hover:bg-indigo-700 shadow-xl shadow-indigo-600/30 transition-all active:scale-95 mt-4">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div id="password_tab" class="tab-content">
            <div class="glass-card p-8">
                <div class="flex items-center gap-3 mb-8 border-b border-slate-200 pb-5">
                    <div class="w-12 h-12 bg-rose-500/10 text-rose-500 rounded-xl flex items-center justify-center"><i class="fas fa-shield-halved text-xl"></i></div>
                    <div>
                        <h3 class="font-black text-slate-900 text-xl tracking-tight italic">Security Settings</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Manage your password</p>
                    </div>
                </div>
                
                <div id="pw_msg"></div>

                <div id="step_1_area" class="space-y-4">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 block">Current Password</label>
                    <div class="flex gap-3">
                        <input type="password" id="curr_pw" class="form-input flex-1" placeholder="Enter current password">
                        <button type="button" onclick="verifyCurrentPw()" class="bg-slate-900 text-white px-8 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-black transition shadow-lg">Verify</button>
                    </div>
                </div>

                <div id="step_2_area" class="hidden space-y-5">
                    <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-xl text-emerald-600 text-xs font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Identity Verified! Set your new password.
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 block mb-2">New Password</label>
                        <input type="password" id="new_pw" class="form-input" placeholder="Min 8 characters">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1 block mb-2">Confirm New Password</label>
                        <input type="password" id="conf_pw" class="form-input" placeholder="Type again to confirm">
                    </div>
                    <button type="button" onclick="finalizeNewPw()" class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-emerald-700 shadow-xl shadow-emerald-600/30 transition active:scale-95 mt-2">
                        Update Password
                    </button>
                </div>
            </div>
        </div>

        <div id="classes_tab" class="tab-content">
            <div class="space-y-4">
                <?php 
                $c_res = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id'");
                if($c_res->num_rows > 0):
                    while($c = $c_res->fetch_assoc()): ?>
                        <div class="glass-card p-4 flex justify-between items-center hover:scale-[1.01] transition-transform">
                            <div class="flex gap-4 items-center">
                                <div class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-600/30">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 text-lg"><?= $c['subject'] ?></p>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5"><?= $c['exam_year'] ?> | Stream: <?= $c['stream'] ?></p>
                                </div>
                            </div>
                            <a href="edit_teacher_profile.php?delete_class=<?= $c['class_id'] ?>" onclick="return confirm('Are you sure you want to delete this class?');" class="w-10 h-10 bg-rose-500/10 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                <i class="fas fa-trash-can"></i>
                            </a>
                        </div>
                    <?php endwhile; 
                else: ?>
                    <p class="text-center text-slate-400 font-bold text-sm py-5">No classes added yet.</p>
                <?php endif; ?>

                <form action="" method="POST" class="glass-card p-6 mt-8 space-y-5 border-indigo-200 bg-indigo-50/50">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-plus-circle text-indigo-600"></i>
                        <h4 class="text-indigo-600 font-black text-xs uppercase tracking-widest">Add New Class</h4>
                    </div>
                    <input type="text" name="subject" placeholder="e.g. Combined Maths" class="form-input" required>
                    <div class="flex gap-3">
                        <input type="text" name="exam_year" placeholder="Year (e.g. 2026)" class="form-input flex-1" required>
                        <select name="stream" class="form-input flex-1 text-slate-600 font-semibold cursor-pointer">
                            <option value="O/L">O/L</option>
                            <option value="A/L">A/L</option>
                        </select>
                    </div>
                    <button type="submit" name="add_class" class="w-full py-4 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-md transition active:scale-95">
                        Create Class
                    </button>
                </form>
            </div>
        </div>

        <div id="fees_tab" class="tab-content">
            <div class="space-y-4">
                <div class="glass-card p-6 text-center border-emerald-100 bg-emerald-50/20 mb-6">
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight italic">Class Fees</h2>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Manage pricing for your classes</p>
                </div>
                
                <form action="" method="POST" class="space-y-4">
                    <?php 
                    $f_res = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id'");
                    if($f_res->num_rows > 0):
                        while($f = $f_res->fetch_assoc()): ?>
                            <div class="glass-card p-5 flex flex-col md:flex-row md:items-center gap-5 hover:scale-[1.01] transition-transform">
                                <div class="flex-1">
                                    <p class="font-black text-slate-900 text-lg"><?= $f['subject'] ?></p>
                                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5"><?= $f['exam_year'] ?> | <?= $f['stream'] ?></p>
                                </div>
                                <div class="flex gap-3">
                                    <div class="w-full md:w-32">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Fee Amount</label>
                                        <input type="number" step="0.01" name="fees[<?= $f['class_id'] ?>][amount]" value="<?= isset($f['class_fee']) ? $f['class_fee'] : '0.00' ?>" class="form-input !py-2.5 font-bold text-slate-700" placeholder="0.00">
                                    </div>
                                    <div class="w-full md:w-36">
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1 mb-1 block">Fee Type</label>
                                        <select name="fees[<?= $f['class_id'] ?>][type]" class="form-input !py-2.5 cursor-pointer font-bold text-slate-600">
                                            <option value="monthly" <?= (isset($f['fee_type']) && $f['fee_type'] == 'monthly') ? 'selected' : '' ?>>Monthly Fee</option>
                                            <option value="day" <?= (isset($f['fee_type']) && $f['fee_type'] == 'day') ? 'selected' : '' ?>>Day Fee</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="pt-4">
                            <button type="submit" name="update_fees" class="w-full py-4 bg-emerald-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-700 shadow-md transition active:scale-95">
                                Save All Fees <i class="fas fa-save ml-1"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="glass-card p-10 text-center opacity-50">
                            <i class="fas fa-chalkboard text-4xl mb-3 text-slate-400"></i>
                            <p class="font-bold text-sm text-slate-500 uppercase tracking-widest">No classes found to add fees.</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div id="towns_tab" class="tab-content">
            <div class="space-y-4">
                <?php 
                $t_res = $conn->query("SELECT * FROM class_towns WHERE teacher_id = '$teacher_id'");
                if($t_res->num_rows > 0):
                    while($t = $t_res->fetch_assoc()): ?>
                        <div class="glass-card p-4 flex justify-between items-center hover:scale-[1.01] transition-transform">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-rose-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-rose-500/30">
                                    <i class="fas fa-location-dot"></i>
                                </div>
                                <div>
                                    <p class="font-black text-slate-900 text-lg"><?= $t['town'] ?></p>
                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5"><?= $t['institute_name'] ?></p>
                                </div>
                            </div>
                            <a href="edit_teacher_profile.php?delete_town=<?= $t['id'] ?>" onclick="return confirm('Are you sure you want to delete this location?');" class="w-10 h-10 bg-rose-500/10 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                <i class="fas fa-trash-can"></i>
                            </a>
                        </div>
                    <?php endwhile; 
                else: ?>
                    <p class="text-center text-slate-400 font-bold text-sm py-5">No locations added yet.</p>
                <?php endif; ?>

                <form action="" method="POST" class="glass-card p-6 mt-8 space-y-5 border-rose-200 bg-rose-50/50">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-map-pin text-rose-600"></i>
                        <h4 class="text-rose-600 font-black text-xs uppercase tracking-widest">Add Teaching Location</h4>
                    </div>
                    <input type="text" name="town" placeholder="City / Town Name" class="form-input" required>
                    <input type="text" name="institute_name" placeholder="Institute Name" class="form-input" required>
                    <button type="submit" name="add_town" class="w-full py-4 bg-rose-500 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-rose-600 shadow-md transition active:scale-95">
                        Add Location
                    </button>
                </form>
            </div>
        </div>

    </main>

    <nav class="bottom-nav">
        <a href="admin_page.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-grid-2 text-xl"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Dash</span>
        </a>
        <a href="student_reg.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-user-plus text-xl"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Reg</span>
        </a>
        <a href="edit_teacher_profile.php" class="flex flex-col items-center text-indigo-400 bg-white/5 px-4 rounded-xl">
            <i class="fas fa-user-gear text-xl"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Settings</span>
        </a>
        <a href="logout.php" class="flex flex-col items-center text-rose-500">
            <i class="fas fa-power-off text-xl"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

    <script>
        // Tab Switcher Logic
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabName).classList.add("active");
            
            if(evt) {
                evt.currentTarget.classList.add("active");
            } else {
                // If called directly via JS (URL Params)
                let tabBtn = document.getElementById('btn_' + tabName);
                if(tabBtn) tabBtn.classList.add("active");
            }
        }

        // Auto-open Tab from URL logic (Tab Persistence)
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab');
            if(activeTab) {
                openTab(null, activeTab);
            }
        });

        // AJAX Password Verification Logic
        function verifyCurrentPw() {
            const cpw = document.getElementById('curr_pw').value;
            const msg = document.getElementById('pw_msg');
            if(!cpw) return;

            fetch('check_pw_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'current_pw=' + encodeURIComponent(cpw)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('step_1_area').classList.add('hidden');
                    document.getElementById('step_2_area').classList.remove('hidden');
                    msg.innerHTML = "";
                } else {
                    msg.innerHTML = `<div class="bg-rose-500/10 text-rose-500 border border-rose-500/20 p-4 rounded-xl mb-4 font-bold text-xs"><i class="fas fa-circle-exclamation mr-1"></i> ${data.message}</div>`;
                }
            });
        }

        function finalizeNewPw() {
            const npw = document.getElementById('new_pw').value;
            const cpw = document.getElementById('conf_pw').value;
            const msg = document.getElementById('pw_msg');

            if(npw !== cpw || npw === "") {
                msg.innerHTML = `<div class="bg-rose-500/10 text-rose-500 border border-rose-500/20 p-4 rounded-xl mb-4 font-bold text-xs"><i class="fas fa-triangle-exclamation mr-1"></i> Passwords do not match!</div>`;
                return;
            }

            fetch('check_pw_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'new_pw=' + encodeURIComponent(npw)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'updated') {
                    msg.innerHTML = `<div class="bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 p-4 rounded-xl mb-4 font-bold text-xs text-center"><i class="fas fa-check-circle mr-1"></i> Password Updated Successfully!</div>`;
                    document.getElementById('step_2_area').classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>