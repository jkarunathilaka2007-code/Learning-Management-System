<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$status_msg = "";

$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");

if (isset($_POST['submit'])) {
    $class_id  = mysqli_real_escape_string($conn, $_POST['class_id']);
    $topic     = mysqli_real_escape_string($conn, $_POST['topic']);
    $live_date = mysqli_real_escape_string($conn, $_POST['live_date']);
    $live_time = mysqli_real_escape_string($conn, $_POST['live_time']);

    // Multiple platforms arrays
    $platforms    = $_POST['media_type']; // Dropdown එකෙන් එන නම
    $admin_links  = $_POST['admin_link'];
    $student_links = $_POST['student_link'];

    $success_count = 0;

    for ($i = 0; $i < count($platforms); $i++) {
        $media = mysqli_real_escape_string($conn, $platforms[$i]);
        $adm   = mysqli_real_escape_string($conn, $admin_links[$i]);
        $std   = mysqli_real_escape_string($conn, $student_links[$i]);

        if (!empty($media) && !empty($std)) {
            // මෙතන $media කියන අගය 'media' column එකට save වෙනවා
            $sql = "INSERT INTO live_classes (teacher_id, class_id, topic, admin_link, meeting_link, media, live_date, live_time) 
                    VALUES ('$teacher_id', '$class_id', '$topic', '$adm', '$std', '$media', '$live_date', '$live_time')";
            
            if (mysqli_query($conn, $sql)) {
                $success_count++;
            }
        }
    }

    if ($success_count > 0) {
        $status_msg = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Live - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid white; }
        .form-input { padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; outline: none; font-size: 13px; font-weight: 700; width: 100%; transition: 0.3s; }
        .platform-box { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-20">

    <div class="bg-slate-900 text-white pb-24 pt-8 px-6 rounded-b-[3rem] shadow-2xl">
        <div class="max-w-2xl mx-auto">
            <a href="live.php" class="bg-white/10 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 w-fit mb-6">
                <i class="fas fa-arrow-left text-rose-400"></i> Back
            </a>
            <h1 class="text-3xl font-black italic uppercase leading-none text-rose-500">Add Live Links</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-2">Database Media Support Enabled</p>
        </div>
    </div>

    <main class="max-w-2xl mx-auto px-4 -mt-12">
        <div class="glass-card shadow-2xl p-6 md:p-10">
            
            <?php if($status_msg == "success"): ?>
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-600 rounded-2xl text-[10px] font-black uppercase flex items-center gap-3">
                    <i class="fas fa-check-circle text-lg"></i> Sessions & Media saved to Database!
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase mb-2">Class</label>
                        <select name="class_id" class="form-input" required>
                            <?php while($c = $classes_list->fetch_assoc()): ?>
                                <option value="<?= $c['class_id'] ?>"><?= $c['exam_year'] ?> | <?= $c['subject'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase mb-2">Lesson Topic</label>
                        <input type="text" name="topic" class="form-input" placeholder="e.g. Lesson 01" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="date" name="live_date" class="form-input" required>
                    <input type="time" name="live_time" class="form-input" required>
                </div>

                <div id="platforms-container" class="space-y-4">
                    <div class="platform-box p-5 bg-slate-50 rounded-3xl border border-slate-200">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-[9px] font-black text-slate-400 uppercase mb-2">Media Platform</label>
                                <select name="media_type[]" class="form-input border-indigo-100">
                                    <option value="Zoom">Zoom Meeting</option>
                                    <option value="YouTube">YouTube Live</option>
                                    <option value="Meet">Google Meet</option>
                                    <option value="Facebook">Facebook Live</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <input type="url" name="admin_link[]" class="form-input border-rose-100" placeholder="Admin Link (Start Class)" required>
                                <input type="url" name="student_link[]" class="form-input border-indigo-100" placeholder="Student Link (Join Class)" required>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addPlatform()" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-2xl text-[10px] font-black text-slate-400 uppercase hover:bg-slate-50 transition-all">
                    <i class="fas fa-plus-circle mr-2"></i> Add Another Media
                </button>

                <button type="submit" name="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] hover:bg-rose-600 transition-all shadow-xl">
                    Save Sessions
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

    <script>
        function addPlatform() {
            const container = document.getElementById('platforms-container');
            const newBox = document.createElement('div');
            newBox.className = 'platform-box p-5 bg-slate-50 rounded-3xl border border-slate-200 relative mt-4';
            newBox.innerHTML = `
                <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 w-6 h-6 bg-rose-500 text-white rounded-full text-[10px] shadow-lg"><i class="fas fa-times"></i></button>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase mb-2">Media Platform</label>
                        <select name="media_type[]" class="form-input border-indigo-100">
                            <option value="Zoom">Zoom Meeting</option>
                            <option value="YouTube">YouTube Live</option>
                            <option value="Meet">Google Meet</option>
                            <option value="Facebook">Facebook Live</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input type="url" name="admin_link[]" class="form-input border-rose-100" placeholder="Admin Link (Start Class)" required>
                        <input type="url" name="student_link[]" class="form-input border-indigo-100" placeholder="Student Link (Join Class)" required>
                    </div>
                </div>
            `;
            container.appendChild(newBox);
        }
    </script>
</body>
</html>