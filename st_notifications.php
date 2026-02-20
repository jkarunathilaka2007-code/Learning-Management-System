<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

/**
 * SQL Query:
 * 1. ශිෂ්‍යයා Enroll වී සිටින පන්තිවල Notifications පමණක් ගනී.
 * 2. classes table එක සමඟ Join කර පන්තියේ නම (subject) ලබා ගනී.
 */
$sql = "SELECT n.*, c.subject, c.exam_year 
        FROM notifications n
        JOIN classes c ON n.class_id = c.class_id
        JOIN student_classes sc ON n.class_id = sc.class_id
        WHERE sc.student_id = '$student_id'";

if (!empty($search_query)) {
    $sql .= " AND (n.title LIKE '%$search_query%' OR n.message LIKE '%$search_query%')";
}

$sql .= " ORDER BY n.created_at DESC";
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-header { background: #0f172a; border-bottom: 4px solid #f59e0b; }
        .notice-card { transition: all 0.3s ease; }
        .notice-card:hover { transform: translateX(5px); }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-24">

    <header class="glass-header pt-8 pb-12 px-5 text-white">
        <div class="max-w-4xl mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Notice Board</h1>
                <p class="text-amber-400 text-[10px] font-bold uppercase tracking-widest">Stay updated with your classes</p>
                <a href="index.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
            </div>
            <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-lg animate-pulse">
                <i class="fas fa-bell text-white"></i>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 -mt-8">
        
        <div class="bg-white p-3 rounded-2xl shadow-lg mb-6 border border-slate-200">
            <form action="" method="GET" class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="<?= $search_query ?>" placeholder="Search notices..." 
                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:ring-2 ring-amber-500/20">
            </form>
        </div>

        <div class="space-y-4">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-sm notice-card flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400">
                                <i class="fas fa-bullhorn text-lg"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-[9px] font-black bg-amber-100 text-amber-700 px-2 py-0.5 rounded uppercase">
                                    <?= $row['subject'] ?> - <?= $row['exam_year'] ?>
                                </span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase">
                                    <?= date('M d, h:i A', strtotime($row['created_at'])) ?>
                                </span>
                            </div>
                            <h3 class="text-sm font-black text-slate-800 uppercase italic mb-1"><?= $row['title'] ?></h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                <?= nl2br($row['message']) ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white py-16 rounded-3xl text-center border-2 border-dashed border-slate-200">
                    <i class="fas fa-comment-slash text-4xl text-slate-200 mb-3"></i>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No new notifications for you</p>
                </div>
            <?php endif; ?>
        </div>
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