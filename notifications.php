<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Notifications list එක ගන්නවා (Classes එක්ක join කරලා)
$sql = "SELECT n.*, c.subject, c.exam_year, c.stream 
        FROM notifications n 
        JOIN classes c ON n.class_id = c.class_id 
        WHERE n.teacher_id = '$teacher_id' 
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - LMS Pro</title>
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
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid white; }
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
            <div class="flex justify-between items-center mb-6">
                <a href="admin_page.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <a href="add_pdf.php" class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 active:scale-90 transition-all">
                    <i class="fas fa-plus text-white"></i>
                </a>
            </div>
            
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Notifications Manager</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Manage Class Notifications</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-5 md:p-8">
            
            <div class="space-y-4">
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-all group">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-all">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div>
                                        <span class="text-[8px] font-black text-indigo-500 uppercase tracking-widest leading-none">
                                            <?= $row['exam_year'] ?> <?= $row['stream'] ?> - <?= $row['subject'] ?>
                                        </span>
                                        <h3 class="text-sm font-black text-slate-800 uppercase italic"><?= $row['title'] ?></h3>
                                    </div>
                                </div>
                                <span class="text-[9px] font-bold text-slate-400 italic"><?= date('M d, h:i A', strtotime($row['created_at'])) ?></span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed ml-1"><?= $row['message'] ?></p>
                            
                            <div class="mt-4 flex justify-end">
                                <a href="delete_notification.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this announcement?')" class="text-[10px] font-black text-rose-500 uppercase tracking-widest hover:underline">
                                    <i class="fas fa-trash-can mr-1"></i> Remove
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="py-20 text-center opacity-30">
                        <i class="fas fa-bullhorn text-4xl mb-3"></i>
                        <p class="text-[10px] font-black uppercase tracking-widest">No Notifications Sent Yet</p>
                    </div>
                <?php endif; ?>
            </div>

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