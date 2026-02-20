<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$st = $conn->query("SELECT * FROM student WHERE id = '$student_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Sidebar styling for Desktop & Mobile toggle */
        .sidebar { 
            width: 280px; background: #0f172a; position: fixed; height: 100vh; 
            z-index: 100; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            left: -280px; /* Hidden by default on mobile */
        }
        
        .sidebar.active { left: 0; }

        @media (min-width: 1024px) {
            .sidebar { left: 0; }
            .main-content { margin-left: 280px; }
            .menu-btn { display: none; }
        }

        /* Fancy Scrolling Cards */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        .nav-item { 
            display: flex; align-items: center; gap: 12px; padding: 14px 20px;
            margin: 8px 16px; border-radius: 12px; color: #94a3b8; font-weight: 600;
            transition: 0.3s; font-size: 14px;
        }
        .nav-item.active { background: #6366f1; color: white; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3); }
        
        /* Profile Image Styling */
        .profile-ring {
            padding: 5px; background: linear-gradient(135deg, #6366f1, #a855f7); border-radius: 9999px;
        }

        .data-card {
            background: white; border-bottom: 1px solid #f1f5f9; padding: 15px 0;
            display: flex; justify-content: space-between; align-items: center;
        }
        @media (max-width: 640px) {
            .data-card { flex-direction: column; align-items: flex-start; gap: 4px; }
        }

        #overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.4); z-index: 90; backdrop-filter: blur(4px);
        }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800">

    <div id="overlay" onclick="toggleMenu()"></div>

    <aside class="sidebar flex flex-col shadow-2xl" id="sidebar">
        <div class="p-8">
            <h1 class="text-white text-2xl font-black italic tracking-tighter">LMS <span class="text-indigo-500">PRO</span></h1>
        </div>
        <nav class="flex-1">
            <a href="index.php" class="nav-item active"><i class="fa fa-note-sticky w-5"></i> Dashboard</a>
            <a href="st_recordings.php" class="nav-item"><i class="fas fa-film"></i> Recordings</a>
            <a href="st_attendance.php" class="nav-item"><i class="fas fa-book w-5"></i> Attendance</a>
            <a href="st_classfees.php" class="nav-item"><i class="fas fa-calculator w-5"></i> Class Fees</a>
            <a href="st_score.php" class="nav-item"><i class="fa fa-layer-group w-5"></i> Papers</a>
            <a href="edit_st_profile.php" class="nav-item"><i class="fas fa-user-gear"></i> Settings</a>
            <a href="Logout.php" class="nav-item"><i class="fas fa-power-off"></i> Logout</a>
        </nav>
    </aside>

    <div class="main-content min-h-screen transition-all">
        
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-40 border-b border-slate-100 px-4 py-4 lg:px-12 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <button onclick="toggleMenu()" class="menu-btn w-10 h-10 flex items-center justify-center bg-slate-100 rounded-xl text-slate-600">
                    <i class="fas fa-bars-staggered"></i>
                </button>
                <h2 class="text-lg font-black text-slate-900 uppercase italic">Control <span class="text-indigo-600">Panel</span></h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase leading-none">Status</p>
                    <p class="text-[11px] font-bold text-emerald-500 uppercase">Online</p>
                </div>
                <img src="uploads/profile_pics/<?= $st['profile_pic'] ?: 'default.png' ?>" class="w-10 h-10 rounded-full object-cover ring-2 ring-indigo-100">
            </div>
        </header>

        <main class="p-4 md:p-10 lg:p-12 max-w-6xl mx-auto">
            
            <div class="bg-white rounded-[2.5rem] p-6 md:p-10 shadow-xl shadow-slate-200/50 mb-8 border border-white">
                <div class="flex flex-col md:flex-row items-center gap-8 text-center md:text-left">
                    <div class="profile-ring shadow-2xl">
                        <img src="uploads/profile_pics/<?= $st['profile_pic'] ?: 'default.png' ?>" class="w-32 h-32 md:w-44 md:h-44 rounded-full object-cover border-4 border-white">
                    </div>
                    <div class="flex-1">
                        <span class="bg-indigo-50 text-indigo-600 px-4 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-100">Verified Student</span>
                        <h1 class="text-2xl md:text-4xl font-black text-slate-900 mt-3 mb-1 uppercase tracking-tighter leading-tight"><?= $st['full_name'] ?></h1>
                        <p class="text-slate-400 font-bold text-sm tracking-widest italic">REG NO: #<?= sprintf("%05d", $st['id']) ?></p>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-1 border-t border-slate-50 pt-6">
                    <div class="data-card">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Gmail Address</span>
                        <span class="text-sm font-bold text-slate-700"><?= $st['gmail'] ?></span>
                    </div>
                    <div class="data-card">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">NIC / ID Number</span>
                        <span class="text-sm font-bold text-slate-700"><?= $st['nic_number'] ?: '---' ?></span>
                    </div>
                    <div class="data-card">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Institute / School</span>
                        <span class="text-sm font-bold text-slate-700"><?= $st['school'] ?: '---' ?></span>
                    </div>
                    <div class="data-card">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Phone Number</span>
                        <span class="text-sm font-bold text-slate-700"><?= $st['contact_number'] ?></span>
                    </div>
                    <div class="data-card border-none">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Town / City</span>
                        <span class="text-sm font-bold text-indigo-600 uppercase italic"><?= $st['town'] ?></span>
                    </div>
                </div>
            </div>

            <div class="mb-10">
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-5 ml-2">Quick Services</h3>
                <div class="flex overflow-x-auto gap-4 pb-6 scrollbar-hide">
                    
                    <a href="st_attendance.php" class="flex-none w-44 bg-gradient-to-br from-indigo-600 to-indigo-800 p-6 rounded-[2rem] shadow-lg shadow-indigo-100 active:scale-95 transition-all text-white">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-4"><i class="fas fa-calendar-check"></i></div>
                        <h4 class="font-black text-[11px] uppercase tracking-wider">Attendance</h4>
                        <p class="text-indigo-200 text-[9px] font-bold mt-1 uppercase">Logs & history</p>
                    </a>

                    <a href="st_classfees.php" class="flex-none w-44 bg-gradient-to-br from-emerald-500 to-emerald-700 p-6 rounded-[2rem] shadow-lg shadow-emerald-100 active:scale-95 transition-all text-white">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-4"><i class="fas fa-wallet"></i></div>
                        <h4 class="font-black text-[11px] uppercase tracking-wider">Payments</h4>
                        <p class="text-emerald-100 text-[9px] font-bold mt-1 uppercase">Fee status</p>
                    </a>

                    <a href="st_score.php" class="flex-none w-44 bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-[2rem] shadow-lg shadow-amber-100 active:scale-95 transition-all text-white">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center mb-4"><i class="fas fa-star"></i></div>
                        <h4 class="font-black text-[11px] uppercase tracking-wider">My Scores</h4>
                        <p class="text-amber-100 text-[9px] font-bold mt-1 uppercase">Paper results</p>
                    </a>

                    <a href="edit_st_profile.php" class="flex-none w-44 bg-slate-800 p-6 rounded-[2rem] shadow-lg shadow-slate-200 active:scale-95 transition-all text-white">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center mb-4"><i class="fas fa-user-edit"></i></div>
                        <h4 class="font-black text-[11px] uppercase tracking-wider">Edit Profile</h4>
                        <p class="text-slate-500 text-[9px] font-bold mt-1 uppercase">Update info</p>
                    </a>
                </div>
            </div>

            <p class="text-center text-[10px] font-black text-slate-300 uppercase tracking-widest mb-10">LMS Version 3.0 • Optimized for Mobile</p>

        </main>
        <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.2)]">
        <a href="index.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Home</span>
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
    </div>

    <script>
        function toggleMenu() {
            $('#sidebar').toggleClass('active');
            $('#overlay').fadeToggle(300);
            if ($('#sidebar').hasClass('active')) {
                $('body').css('overflow', 'hidden');
            } else {
                $('body').css('overflow', 'auto');
            }
        }
    </script>
</body>
</html>