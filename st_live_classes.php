<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$sql = "SELECT lc.*, c.subject, c.exam_year, c.stream 
        FROM live_classes lc 
        JOIN classes c ON lc.class_id = c.class_id 
        JOIN student_classes sc ON lc.class_id = sc.class_id 
        WHERE sc.student_id = '$student_id' 
        AND lc.status IN ('Live', 'Upcoming') 
        ORDER BY lc.status ASC, lc.live_date ASC, lc.live_time ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Sessions - LMS Pro</title>
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
        .live-pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { transform: scale(0.95); opacity: 1; } 50% { transform: scale(1.05); opacity: 0.5; } 100% { transform: scale(0.95); opacity: 1; } }
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 60px; background: #0f172a; display: flex; justify-content: space-around; align-items: center; z-index: 1000; }
    </style>
</head>
<body class="pb-24">

    <div class="bg-slate-900 text-white pb-20 pt-6 px-6 rounded-b-[2.5rem] shadow-xl relative">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div>
                <a href="index.php" class="text-[10px] font-black uppercase tracking-widest text-indigo-400 flex items-center gap-1 mb-1">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <h1 class="text-2xl font-black italic tracking-tighter uppercase leading-none">Live Hub</h1>
            </div>
            <div class="w-10 h-10 bg-rose-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-satellite-dish text-white text-sm"></i>
            </div>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-3 -mt-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    $is_live = ($row['status'] == 'Live');
                    $dt_string = $row['live_date'] . ' ' . $row['live_time'];
                ?>
                    <div class="glass-card shadow-lg p-4 flex flex-col justify-between transition-all active:scale-[0.98]">
                        
                        <div class="flex justify-between items-center mb-3">
                            <?php if($is_live): ?>
                                <span class="bg-rose-100 text-rose-600 text-[8px] font-black px-2 py-1 rounded-md uppercase flex items-center gap-1 live-pulse">
                                    <i class="fas fa-circle text-[5px]"></i> Live
                                </span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-500 text-[8px] font-black px-2 py-1 rounded-md uppercase">
                                    Upcoming
                                </span>
                            <?php endif; ?>
                            
                            <span class="text-[9px] font-bold text-slate-400 uppercase italic">
                                <i class="<?= ($row['media'] == 'Zoom') ? 'fas fa-video text-blue-500' : 'fab fa-youtube text-red-500' ?> mr-1"></i>
                                <?= $row['media'] ?>
                            </span>
                        </div>

                        <div class="mb-3">
                            <h3 class="text-sm font-black text-slate-800 uppercase italic leading-tight mb-1 truncate">
                                <?= $row['topic'] ?>
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[8px] font-black px-1.5 py-0.5 bg-indigo-50 text-indigo-600 rounded">
                                    <?= $row['exam_year'] ?>
                                </span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase truncate">
                                    <?= $row['subject'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-2 bg-slate-50 rounded-xl border border-slate-100 mb-3">
                            <div class="flex items-center gap-2">
                                <i class="far fa-calendar-alt text-indigo-500 text-[10px]"></i>
                                <span class="text-[9px] font-black text-slate-600"><?= date('M d', strtotime($row['live_date'])) ?></span>
                            </div>
                            <div class="flex items-center gap-2 border-l pl-2 border-slate-200">
                                <i class="far fa-clock text-indigo-500 text-[10px]"></i>
                                <span class="text-[9px] font-black text-slate-600"><?= date('h:i A', strtotime($row['live_time'])) ?></span>
                            </div>
                        </div>

                        <?php if($is_live): ?>
                            <a href="<?= $row['meeting_link'] ?>" target="_blank" class="w-full bg-slate-900 text-white text-center py-2.5 rounded-xl font-black text-[9px] uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md">
                                <i class="fas fa-play mr-1"></i> Join Now
                            </a>
                        <?php else: ?>
                            <div class="flex flex-col gap-1">
                                <span class="text-[7px] font-black text-slate-400 uppercase text-center countdown-timer" data-time="<?= $dt_string ?>">
                                    Loading...
                                </span>
                                <button disabled class="w-full bg-slate-100 text-slate-400 py-2.5 rounded-xl font-black text-[9px] uppercase border border-slate-200/50 cursor-not-allowed">
                                    <i class="fas fa-lock mr-1"></i> Locked
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-10 glass-card text-center opacity-50">
                    <p class="text-[9px] font-black uppercase tracking-widest">No classes found</p>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-lg">
        <a href="index.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-house-chimney text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Home</span>
        </a>
        <a href="student_page.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-user text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Profile</span>
        </a>
        <a href="logout.php" class="text-rose-500 flex flex-col items-center">
            <i class="fas fa-power-off text-base"></i>
            <span class="text-[8px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

    <script>
        function updateCountdowns() {
            document.querySelectorAll('.countdown-timer').forEach(el => {
                const target = new Date(el.getAttribute('data-time')).getTime();
                const now = new Date().getTime();
                const diff = target - now;
                if (diff <= 0) { el.innerHTML = "Starting Soon"; return; }
                const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((diff % (1000 * 60)) / 1000);
                el.innerHTML = `Starts in: ${h}h ${m}m ${s}s`;
            });
        }
        setInterval(updateCountdowns, 1000);
        updateCountdowns();
    </script>
</body>
</html>