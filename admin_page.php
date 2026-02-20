<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Data Fetching
$teacher = $conn->query("SELECT * FROM teacher WHERE id = '$teacher_id'")->fetch_assoc();
$student_count = $conn->query("SELECT COUNT(id) as total FROM student")->fetch_assoc()['total'];
$classes_res = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id'");
$towns_res = $conn->query("SELECT * FROM class_towns WHERE teacher_id = '$teacher_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Pro - Ultra Glass Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        }

        /* Sidebar Fix - Scrollable but no bar */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: 260px; background: rgba(15, 23, 42, 0.95); 
            backdrop-filter: blur(10px); color: white;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1001;
            /* New properties added below */
            overflow-y: auto; 
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }
        
        /* Hide scrollbar for Chrome, Safari and Opera */
        .sidebar::-webkit-scrollbar {
            display: none;
        }

        @media (max-width: 1023px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); box-shadow: 20px 0 50px rgba(0,0,0,0.3); }
            main { margin-left: 0 !important; }
        }

        @media (min-width: 1024px) {
            main { margin-left: 260px; }
        }

        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 25px; color: #94a3b8; transition: 0.3s;
            font-size: 13px; font-weight: 500;
        }
        .nav-link.active { background: rgba(99, 102, 241, 0.2); color: #818cf8; border-left: 4px solid #818cf8; }

        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="antialiased text-slate-800">

    <div class="overlay fixed inset-0 bg-black/40 backdrop-blur-sm z-[1000] hidden" id="overlay" onclick="toggleSidebar()"></div>

    <aside class="sidebar shadow-2xl" id="sidebar">
        <div class="p-8 flex items-center gap-3 border-b border-white/10 sticky top-0 bg-[#0f172a]/95 backdrop-blur-md z-10">
            <i class="fas fa-graduation-cap text-indigo-400 text-xl"></i>
            <span class="text-lg font-black tracking-tight italic uppercase text-white">LMS <span class="text-indigo-400">PRO</span></span>
        </div>
        <nav class="mt-8 flex flex-col gap-1 pb-20"> <a href="index.php" class="nav-link"><i class="fas fa-house-chimney w-5"></i> <span>Home</span></a>
            <a href="admin_page.php" class="nav-link active"><i class="fa fa-note-sticky w-5"></i> <span>Dashboard</span></a>
            <a href="approve.php" class="nav-link"><i class="fas fa-user-plus w-5"></i> <span>Approve Accounts</span></a>
            <a href="student_info.php" class="nav-link"><i class="fas fa-user w-5"></i> <span>Students</span></a>
            <a href="attendance.php" class="nav-link"><i class="fas fa-book w-5"></i> <span>Attendance</span></a>
            <a href="class_fees.php" class="nav-link"><i class="fas fa-calculator w-5"></i> <span>Class Fees</span></a>
            <a href="view_marks_report.php" class="nav-link"><i class="fa fa-layer-group w-5"></i> <span>Paper Marks</span></a>
            <a href="teachers_recordings.php" class="nav-link"><i class="fas fa-laptop w-5"></i> <span>Recordings</span></a>
            <a href="live_classes.php" class="nav-link"><i class="fas fa-camera w-5"></i> <span>Live Classes</span></a>
            <a href="pdf.php" class="nav-link"><i class="fa fa-file-pdf w-5"></i> <span>Tutes</span></a>
            <a href="p_papers.php" class="nav-link"><i class="fa fa-book w-5"></i> <span>Pat Papers</span></a>
            <a href="notifications.php" class="nav-link"><i class="fa fa-bell w-5"></i> <span>Notifications</span></a>
            <a href="edit_teacher_profile.php" class="nav-link"><i class="fas fa-gear w-5"></i> <span>Settings</span></a>
            <a href="logout.php" class="nav-link text-rose-400 border-t border-white/5 mt-4"><i class="fas fa-power-off w-5"></i> <span>Logout</span></a>
        </nav>
    </aside>

    <header class="lg:hidden p-4 flex justify-between items-center bg-white/60 backdrop-blur-md sticky top-0 z-[999] border-b border-white/20">
        <button onclick="toggleSidebar()" class="w-10 h-10 flex items-center justify-center bg-slate-900 text-white rounded-xl shadow-lg">
            <i class="fas fa-bars-staggered"></i>
        </button>
        <span class="font-black text-slate-900 italic tracking-tighter">DASHBOARD</span>
        <div class="w-10 h-10 rounded-full border-2 border-white shadow-sm overflow-hidden">
            <img src="uploads/profile_pics/<?= $teacher['profile_pic'] ?: 'default_user.png' ?>" class="w-full h-full object-cover">
        </div>
    </header>

    <main class="p-6 lg:p-12 pb-32">
        <div class="flex flex-col items-center mb-12">
            <div class="relative p-2 bg-white/30 backdrop-blur-md rounded-full shadow-2xl border border-white/50">
                <img src="uploads/profile_pics/<?= $teacher['profile_pic'] ?: 'default_user.png' ?>" 
                     class="w-32 h-32 lg:w-48 lg:h-48 rounded-full object-cover border-4 border-white shadow-inner">
            </div>
            <div class="text-center mt-6">
                <h1 class="text-3xl lg:text-5xl font-black text-slate-900 tracking-tighter uppercase italic drop-shadow-sm"><?= $teacher['name'] ?></h1>
                <p class="text-indigo-600 font-bold uppercase tracking-[0.3em] text-[10px] mt-2 opacity-80"><?= $teacher['gmail'] ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-12">
            <div class="glass-card p-6 text-center">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Students</p>
                <h3 class="text-3xl font-black text-slate-900"><?= $student_count ?></h3>
            </div>
            <div class="glass-card p-6 text-center">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">NIC No</p>
                <h3 class="text-[12px] font-bold text-indigo-600"><?= $teacher['nic_number'] ?: '---' ?></h3>
            </div>
            <div class="glass-card p-6 text-center col-span-2 lg:col-span-1">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Contact</p>
                <h3 class="text-sm font-black text-slate-900"><?= $teacher['contact_number'] ?: '---' ?></h3>
            </div>
        </div>

        <?php 
        $sliders = [
            ['title' => 'Academic Classes', 'res' => $classes_res, 'id' => 'c-scroll', 'icon' => 'fa-book-open', 'bg' => 'bg-indigo-500'],
            ['title' => 'Coverage Towns', 'res' => $towns_res, 'id' => 't-scroll', 'icon' => 'fa-location-dot', 'bg' => 'bg-rose-500']
        ];
        foreach ($sliders as $s): ?>
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4 px-2">
                <h4 class="text-[11px] font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full <?= $s['bg'] ?>"></span> <?= $s['title'] ?>
                </h4>
                <div class="flex gap-2">
                    <button onclick="scrollSect('<?= $s['id'] ?>', -200)" class="w-8 h-8 rounded-full bg-white/80 backdrop-blur shadow-sm flex items-center justify-center text-slate-400 active:scale-90"><i class="fas fa-arrow-left text-xs"></i></button>
                    <button onclick="scrollSect('<?= $s['id'] ?>', 200)" class="w-8 h-8 rounded-full bg-white/80 backdrop-blur shadow-sm flex items-center justify-center text-slate-400 active:scale-90"><i class="fas fa-arrow-right text-xs"></i></button>
                </div>
            </div>
            <div id="<?= $s['id'] ?>" class="flex overflow-x-auto gap-5 px-2 no-scrollbar scroll-smooth">
                <?php while($row = $s['res']->fetch_assoc()): ?>
                <div class="min-w-[170px] lg:min-w-[240px] glass-card p-8 flex flex-col items-center hover:scale-105 transition duration-300 shadow-lg">
                    <div class="w-12 h-12 <?= $s['bg'] ?> text-white rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-indigo-500/20">
                        <i class="fas <?= $s['icon'] ?>"></i>
                    </div>
                    <p class="font-black text-xs text-center uppercase tracking-tight text-slate-900"><?= $row['subject'] ?? $row['town'] ?></p>
                    <p class="text-[10px] font-bold text-slate-400 mt-2"><?= $row['exam_year'] ?? $row['institute_name'] ?></p>
                </div>
                <?php endwhile; $s['res']->data_seek(0); ?>
            </div>
        </div>
        <?php endforeach; ?>
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
        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('overlay');
            sb.classList.toggle('active');
            ov.classList.toggle('hidden');
        }

        function scrollSect(id, val) {
            document.getElementById(id).scrollBy({ left: val, behavior: 'smooth' });
        }
    </script>
</body>
</html>