<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

/**
 * SQL Query:
 * 1. folder_name එක 'Paper Discussions' වන දත්ත පමණක් ලබා ගනී.
 * 2. ශිෂ්‍යයා ලියාපදිංචි වී ඇති පන්ති වලට අදාළ විය යුතුය.
 * 3. Status එක Active විය යුතුය.
 */
$sql = "SELECT r.*, c.subject, c.exam_year, c.stream 
        FROM recordings r 
        JOIN classes c ON r.class_id = c.class_id 
        JOIN student_classes sc ON r.class_id = sc.class_id 
        WHERE sc.student_id = '$student_id' 
        AND r.folder_name = 'Paper Discussions' 
        AND r.status = 'Active' 
        AND (r.expire_date >= '$current_date' OR r.expire_date IS NULL)
        ORDER BY r.added_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paper Discussions - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 1.5rem; border: 1px solid white; }
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 60px; background: #0f172a; display: flex; justify-content: space-around; align-items: center; z-index: 1000; }
        .paper-thumb { background: linear-gradient(45deg, #4338ca, #6366f1); }
    </style>
</head>
<body class="pb-24">

    <div class="bg-indigo-900 text-white pb-20 pt-6 px-6 rounded-b-[2.5rem] shadow-xl relative">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div>
                <a href="index.php" class="text-[10px] font-black uppercase tracking-widest text-indigo-300 flex items-center gap-1 mb-1">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h1 class="text-2xl font-black italic tracking-tighter uppercase leading-none">Paper Hub</h1>
            </div>
            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center border border-white/20">
                <i class="fas fa-file-signature text-white text-sm"></i>
            </div>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-3 -mt-10">
        
        <div class="mb-4 flex items-center gap-2 ml-2">
            <span class="h-2 w-2 bg-indigo-500 rounded-full"></span>
            <h2 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Paper Discussions Only</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    
                    <div class="glass-card shadow-lg overflow-hidden flex flex-col transition-all active:scale-[0.98]">
                        
                        <div class="relative h-28 paper-thumb flex items-center justify-center group">
                            <i class="fas fa-file-video text-white/20 text-4xl group-hover:scale-110 transition-all"></i>
                            
                            <?php if(!empty($row['duration'])): ?>
                                <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[8px] font-bold px-1.5 py-0.5 rounded">
                                    <?= $row['duration'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="p-4">
                            <div class="mb-3">
                                <div class="flex justify-between items-start mb-1">
                                    <p class="text-[8px] font-black text-indigo-600 uppercase">
                                        <?= $row['exam_year'] ?> | <?= $row['subject'] ?>
                                    </p>
                                </div>
                                <h3 class="text-sm font-black text-slate-800 uppercase italic leading-tight truncate">
                                    <?= $row['video_title'] ?>
                                </h3>
                                <?php if(!empty($row['sub_title'])): ?>
                                    <p class="text-[9px] font-medium text-slate-400 mt-1 italic truncate">
                                        <?= $row['sub_title'] ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-4 pt-3 border-t border-slate-100 mb-4">
                                <div class="flex items-center gap-1.5">
                                    <i class="far fa-clock text-slate-400 text-[9px]"></i>
                                    <span class="text-[9px] font-bold text-slate-500">Added: <?= date('M d', strtotime($row['added_date'])) ?></span>
                                </div>
                            </div>

                            <a href="watch_video.php?id=<?= $row['id'] ?>" class="block w-full bg-slate-900 text-white text-center py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md shadow-indigo-100">
                                <i class="fas fa-glasses mr-1"></i> View Discussion
                            </a>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 glass-card text-center border-dashed border-2 border-slate-200 bg-transparent">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-slate-300 text-xl"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No Paper Discussions Found</p>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-lg">
        <a href="index.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-house-chimney text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Home</span>
        </a>
        <a href="st_recordings.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-play-circle text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Videos</span>
        </a>
        <a href="st_discussions.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-file-signature text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Papers</span>
        </a>
        <a href="logout.php" class="text-rose-500 flex flex-col items-center">
            <i class="fas fa-power-off text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

</body>
</html>