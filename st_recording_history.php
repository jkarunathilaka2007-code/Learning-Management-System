<?php
session_start();
include 'config.php';

date_default_timezone_set("Asia/Colombo");

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// --- මුළු කාලය (Grand Total) ලබා ගැනීම ---
$total_time_sql = "SELECT SUM(duration_watched) as grand_total FROM recording_history WHERE student_id = '$student_id'";
$total_time_res = $conn->query($total_time_sql);
$grand_total_seconds = $total_time_res->fetch_assoc()['grand_total'] ?? 0;

// --- History Query ---
$history_sql = "SELECT 
                    DATE(h.start_time) as watch_date,
                    h.recording_id,
                    r.video_title,
                    r.sub_title,
                    r.folder_name,
                    c.subject,
                    MAX(h.start_time) as last_watched,
                    SUM(h.duration_watched) as total_duration
                FROM recording_history h
                JOIN recordings r ON h.recording_id = r.id
                JOIN classes c ON r.class_id = c.class_id
                WHERE h.student_id = '$student_id'
                GROUP BY watch_date, h.recording_id
                ORDER BY last_watched DESC";

$history_res = $conn->query($history_sql);

function formatSeconds($seconds) {
    if ($seconds <= 0) return "0s";
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    
    $out = "";
    if ($h > 0) $out .= $h . "h ";
    if ($m > 0) $out .= $m . "m ";
    if ($s > 0 || $out == "") $out .= $s . "s";
    return trim($out);
}

function getDayLabel($date) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    if ($date == $today) return "Today";
    if ($date == $yesterday) return "Yesterday";
    return date('M d', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>History | LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .history-card { background: white; transition: transform 0.1s ease; border: 1px solid #f1f5f9; }
        .history-card:active { transform: scale(0.98); background: #f9fafb; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-24">

    <header class="bg-[#0f172a] text-white pt-8 pb-12 px-5 rounded-b-[2rem] shadow-lg">
        <div class="max-w-3xl mx-auto">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="st_recordings.php" class="w-9 h-9 flex items-center justify-center bg-white/10 rounded-xl">
                        <i class="fas fa-arrow-left text-xs"></i>
                    </a>
                    <div>
                        <h1 class="text-lg font-extrabold uppercase tracking-tight">Watch <span class="text-indigo-400">History</span></h1>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Learning Progress</p>
                    </div>
                </div>
                <div class="text-right bg-white/5 px-4 py-2 rounded-2xl border border-white/10">
                    <p class="text-[7px] font-black text-indigo-400 uppercase mb-0.5 tracking-tighter">Total Time</p>
                    <p class="text-xs font-black"><?= formatSeconds($grand_total_seconds) ?></p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 -mt-4">
        <?php 
        $current_day = "";
        if ($history_res->num_rows > 0):
            while($row = $history_res->fetch_assoc()): 
                if ($current_day != $row['watch_date']): 
                    $current_day = $row['watch_date'];
        ?>
                    <div class="flex items-center gap-3 mt-8 mb-4 px-2">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <?= getDayLabel($current_day) ?>
                        </span>
                        <div class="h-[1px] bg-slate-200/60 flex-1"></div>
                    </div>
        <?php endif; ?>

                <a href="watch_video.php?id=<?= $row['recording_id'] ?>" class="history-card flex items-center p-3 rounded-2xl mb-2.5 shadow-sm">
                    <div class="w-10 h-10 bg-slate-50 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 border border-slate-100">
                        <i class="fas fa-play text-[10px]"></i>
                    </div>
                    
                    <div class="flex-1 ml-4 min-w-0">
                        <h4 class="text-[11px] font-bold text-slate-800 uppercase truncate mb-0.5">
                            <?= $row['video_title'] ?>
                        </h4>
                        <div class="flex items-center gap-2 overflow-hidden">
                            <span class="text-[8px] font-black text-indigo-500 uppercase flex-shrink-0"><?= $row['subject'] ?></span>
                            <span class="text-slate-300 text-[8px]">•</span>
                            <span class="text-[9px] font-medium text-slate-400 truncate italic"><?= $row['sub_title'] ?></span>
                        </div>
                    </div>

                    <div class="ml-2 text-right">
                        <div class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg border border-indigo-100">
                            <p class="text-[9px] font-black tracking-tighter"><?= formatSeconds($row['total_duration']) ?></p>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-24">
                <i class="fas fa-history text-slate-200 text-3xl mb-3"></i>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">No history records yet</p>
            </div>
        <?php endif; ?>
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

</body>
</html>