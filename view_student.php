<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: student_info.php");
    exit();
}

$student_id = mysqli_real_escape_string($conn, $_GET['id']);

// ශිෂ්‍යයාගේ දත්ත ලබා ගැනීම
$sql = "SELECT * FROM student WHERE id = '$student_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Student not found!";
    exit();
}

$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - <?= $student['full_name'] ?></title>
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
        .glass-card { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(15px); 
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 2.5rem;
        }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
        
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-5">

    <div class="max-w-md w-full">
        <a href="student_info.php" class="inline-flex items-center gap-2 text-slate-500 font-bold text-xs uppercase tracking-widest mb-6 hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Directory
        </a>

        <div class="glass-card shadow-2xl overflow-hidden relative">
            <div class="h-32 bg-gradient-to-r from-indigo-600 to-purple-600"></div>
            
            <div class="px-8 pb-10">
                <div class="relative -mt-16 mb-6">
                    <div class="w-32 h-32 bg-white rounded-[2rem] p-2 shadow-xl mx-auto">
                        <div class="w-full h-full bg-slate-100 rounded-[1.5rem] flex items-center justify-center text-indigo-600 text-4xl font-black shadow-inner">
                            <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                        </div>
                    </div>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight"><?= $student['full_name'] ?></h2>
                    <span class="inline-block bg-indigo-100 text-indigo-600 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full mt-2">
                        ID: ST-<?= $student['id'] ?>
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Gmail Address</p>
                            <p class="text-sm font-bold text-slate-700"><?= $student['gmail'] ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                            <i class="fas fa-phone text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Contact Number</p>
                            <p class="text-sm font-bold text-slate-700"><?= $student['contact_number'] ?></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-slate-400 shadow-sm">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Registration Date</p>
                            <p class="text-sm font-bold text-slate-700"><?= date('F d, Y', strtotime($student['created_at'])) ?></p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-8">
                    <a href="tel:<?= $student['contact_number'] ?>" class="flex items-center justify-center gap-2 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                        <i class="fas fa-phone"></i> Call
                    </a>
                    <a href="mailto:<?= $student['gmail'] ?>" class="flex items-center justify-center gap-2 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-slate-200">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                </div>
            </div>
        </div>
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
    </div>
    

</body>
</html>