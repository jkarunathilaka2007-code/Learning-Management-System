<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$st = $conn->query("SELECT full_name FROM student WHERE id = '$student_id'")->fetch_assoc();

// Filter අගයන් ලබා ගැනීම
$filter_subject = isset($_GET['subject']) ? mysqli_real_escape_string($conn, $_GET['subject']) : '';
$filter_month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : '';

// Attendance Query with Join
$sql = "SELECT a.*, c.subject, c.exam_year, c.stream 
        FROM attendance a 
        JOIN classes c ON a.class_id = c.class_id 
        WHERE a.student_id = '$student_id'";

if (!empty($filter_subject)) { $sql .= " AND c.subject = '$filter_subject'"; }
if (!empty($filter_month)) { $sql .= " AND MONTH(a.date) = '$filter_month'"; }

$sql .= " ORDER BY a.date DESC";
$result = $conn->query($sql);

$subjects_list = $conn->query("SELECT DISTINCT subject FROM classes");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance - LMS Pro</title>
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
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2.5rem; border: 1px solid white; }
        .filter-input { 
            padding: 12px; border-radius: 14px; border: 1px solid #e2e8f0; 
            outline: none; font-size: 13px; font-weight: 700; background: white;
            width: 100%; transition: 0.3s;
        }
        .filter-input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        
        /* Custom Scrollbar Hide */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-10">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3.5rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <a href="student_page.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-500 uppercase leading-none">Logged as</p>
                    <p class="text-[11px] font-bold text-indigo-300 uppercase"><?= explode(' ', $st['full_name'])[0] ?></p>
                </div>
            </div>
            
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">My <span class="text-indigo-500">Attendance</span></h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2 italic">Student Participation Logs</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-5 md:p-8">
            
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10 items-end">
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1 text-center md:text-left">Subject</label>
                    <select name="subject" class="filter-input">
                        <option value="">All Subjects</option>
                        <?php while($sub = $subjects_list->fetch_assoc()): ?>
                            <option value="<?= $sub['subject'] ?>" <?= ($filter_subject == $sub['subject']) ? 'selected' : '' ?>>
                                <?= $sub['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1 text-center md:text-left">Month</label>
                    <select name="month" class="filter-input">
                        <option value="">Any Month</option>
                        <?php for($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($filter_month == $m) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-3.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 shadow-lg shadow-slate-200 transition-all">
                        Filter
                    </button>
                    <?php if($filter_subject || $filter_month): ?>
                        <a href="st_attendance.php" class="bg-rose-50 text-rose-500 px-4 py-3.5 rounded-xl flex items-center justify-center hover:bg-rose-100 transition-all">
                            <i class="fas fa-undo-alt"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-container scrollbar-hide">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest">
                            <th class="px-6 py-2">Date Info</th>
                            <th class="px-6 py-2">Class Details</th>
                            <th class="px-6 py-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="bg-white hover:bg-slate-50/50 transition-all rounded-3xl group shadow-sm border border-slate-100">
                                    <td class="px-6 py-5 first:rounded-l-[1.5rem]">
                                        <div class="flex items-center gap-4">
                                            <div class="w-11 h-11 bg-slate-100 rounded-2xl flex flex-col items-center justify-center text-slate-500 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                                <span class="text-[8px] uppercase"><?= date('M', strtotime($row['date'])) ?></span>
                                                <span class="text-sm"><?= date('d', strtotime($row['date'])) ?></span>
                                            </div>
                                            <div>
                                                <p class="text-[11px] font-black text-slate-800 uppercase leading-none"><?= date('l', strtotime($row['date'])) ?></p>
                                                <p class="text-[9px] text-slate-400 font-bold mt-1"><?= date('Y', strtotime($row['date'])) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <h3 class="text-xs font-black text-slate-800 uppercase italic">
                                            <?= $row['subject'] ?>
                                        </h3>
                                        <p class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5">
                                            <?= $row['exam_year'] ?> <?= $row['stream'] ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-5 last:rounded-r-[1.5rem] text-center">
                                        <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-600 px-3 py-1.5 rounded-full border border-emerald-100">
                                            <div class="w-1 h-1 bg-emerald-500 rounded-full animate-pulse"></div>
                                            <span class="text-[9px] font-black uppercase italic tracking-wider">Present</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-24 text-center">
                                    <div class="opacity-20">
                                        <i class="fas fa-calendar-xmark text-5xl mb-4"></i>
                                    </div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No attendance found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center mt-12 mb-8">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] opacity-50">LMS PRO • Student Analytics</p>
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