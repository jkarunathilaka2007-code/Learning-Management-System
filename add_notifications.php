<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message_status = "";

// Dropdown එකට classes list එක ගන්නවා
$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");

if (isset($_POST['submit'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $title    = mysqli_real_escape_string($conn, $_POST['title']);
    $msg_body = mysqli_real_escape_string($conn, $_POST['message']);

    if ($class_id === "broadcast") {
        // --- Broadcast Logic ---
        // ගුරුවරයාගේ හැම පන්තියක්ම අරගෙන එකින් එකට insert කරනවා
        $all_classes = $conn->query("SELECT class_id FROM classes WHERE teacher_id = '$teacher_id'");
        
        if ($all_classes->num_rows > 0) {
            $success_count = 0;
            while ($row = $all_classes->fetch_assoc()) {
                $c_id = $row['class_id'];
                $insert_sql = "INSERT INTO notifications (teacher_id, class_id, title, message) 
                               VALUES ('$teacher_id', '$c_id', '$title', '$msg_body')";
                if (mysqli_query($conn, $insert_sql)) {
                    $success_count++;
                }
            }
            if ($success_count > 0) { $message_status = "success_broadcast"; }
        } else {
            $message_status = "no_classes";
        }
    } else {
        // --- Single Class Logic ---
        $sql = "INSERT INTO notifications (teacher_id, class_id, title, message) 
                VALUES ('$teacher_id', '$class_id', '$title', '$msg_body')";
        
        if (mysqli_query($conn, $sql)) {
            $message_status = "success";
        } else {
            $message_status = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Notification - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%); min-height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid white; }
        .form-input { padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; outline: none; font-size: 13px; font-weight: 700; width: 100%; transition: 0.3s; }
        .form-input:focus { border-color: #e2e8f0; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }
        input[type="radio"]:checked + label { background-color: #0f172a; color: white; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen pb-28">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6 text-[10px] font-black uppercase tracking-widest">
                <a href="notifications.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <span class="opacity-50 italic">New Notification</span>
            </div>
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Add Notificaion</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Privet or Broatcast massegers for classes</p>
        </div>
    </div>

    <main class="max-w-2xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-6 md:p-10">
            
            <?php if($message_status == "success"): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl flex items-center gap-3 italic font-black text-[10px] uppercase">
                    <i class="fas fa-check-circle"></i> Sent Successfully!
                </div>
            <?php elseif($message_status == "success_broadcast"): ?>
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-2xl flex items-center gap-3 italic font-black text-[10px] uppercase animate-pulse">
                    <i class="fas fa-tower-broadcast"></i> Broadcasted to all classes!
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Select Target</label>
                    <select name="class_id" class="form-input border-2 border-amber-100" required>
                        <optgroup label="Global Options">
                            <option value="broadcast" class="font-black text-amber-600">BROADCAST TO ALL CLASSES</option>
                        </optgroup>
                        <optgroup label="Specific Classes">
                            <?php while($c = $classes_list->fetch_assoc()): ?>
                                <option value="<?= $c['class_id'] ?>"><?= $c['exam_year'] ?> | <?= $c['stream'] ?> | <?= $c['subject'] ?></option>
                            <?php endwhile; ?>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Holiday Notice" required>
                </div>

                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Message Content</label>
                    <textarea name="message" rows="5" class="form-input" placeholder="Type your message here..." required></textarea>
                </div>

                <button type="submit" name="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] hover:bg-amber-500 shadow-xl transition-all active:scale-95">
                    Send Announcement
                </button>
            </form>
        </div>
    </main>
    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.2)]">
        <a href="index.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Home</span>
        </a>
        <a href="admin_page.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-user text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Profile</span>
        </a>
        <a href="logout.php" class="text-rose-500 flex flex-col items-center">
            <i class="fas fa-power-off text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

</body>
</html>