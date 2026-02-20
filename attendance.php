<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Filter values gannawa
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$filter_date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';

// Base SQL Query
$sql = "SELECT a.date, a.class_id, c.subject, c.exam_year, c.stream, COUNT(a.student_id) as present_count
        FROM attendance a 
        JOIN classes c ON a.class_id = c.class_id 
        WHERE c.teacher_id = '$teacher_id'";

if (!empty($filter_class)) {
    $sql .= " AND a.class_id = '$filter_class'";
}
if (!empty($filter_date)) {
    $sql .= " AND a.date = '$filter_date'";
}

$sql .= " GROUP BY a.date, a.class_id ORDER BY a.date DESC";
$result = $conn->query($sql);

// Class list dropdown ekata
$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Logs - LMS Pro</title>
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
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid white; }
        .filter-input { 
            padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; 
            outline: none; font-size: 13px; font-weight: 700; background: white;
            width: 100%; transition: 0.3s;
        }
        /* Mobile table scroll protection */
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
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
                <a href="mark_attendance.php" class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 active:scale-90">
                    <i class="fas fa-plus text-white"></i>
                </a>
            </div>
            
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Attendance</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Manage & Filter Records</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-5 md:p-8">
            
            <form action="" method="GET" class="space-y-4 md:space-y-0 md:flex md:gap-3 mb-8 items-end">
                <div class="flex-1">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Class</label>
                    <select name="class_id" class="filter-input">
                        <option value="">All Classes</option>
                        <?php while($c = $classes_list->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($filter_class == $c['class_id']) ? 'selected' : '' ?>>
                                <?= $c['exam_year'] ?> <?= $c['stream'] ?> <?= $c['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Date</label>
                    <input type="date" name="date" class="filter-input" value="<?= $filter_date ?>">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 md:flex-none px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all">
                        Filter
                    </button>
                    <?php if($filter_class || $filter_date): ?>
                        <a href="attendance.php" class="bg-rose-50 text-rose-500 px-4 py-3 rounded-xl flex items-center justify-center transition-all">
                            <i class="fas fa-redo-alt"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-container">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest">
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Class</th>
                            <th class="px-4 py-2 text-center">Qty</th>
                            <th class="px-4 py-2 text-right">View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="bg-white hover:bg-slate-50 transition-all rounded-2xl group shadow-sm border border-slate-100">
                                    <td class="px-4 py-4 first:rounded-l-2xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-slate-100 rounded-lg flex flex-col items-center justify-center text-slate-500 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-all shrink-0">
                                                <span class="text-[8px] uppercase"><?= date('M', strtotime($row['date'])) ?></span>
                                                <span class="text-xs"><?= date('d', strtotime($row['date'])) ?></span>
                                            </div>
                                            <span class="hidden sm:inline text-[11px] font-black text-slate-700 uppercase"><?= date('l', strtotime($row['date'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <h3 class="text-xs font-black text-slate-800 uppercase italic truncate max-w-[120px] md:max-w-none">
                                            <?= $row['exam_year'] ?> <?= $row['stream'] ?>
                                        </h3>
                                        <p class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest mt-0.5"><?= $row['subject'] ?></p>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm font-black text-slate-800"><?= $row['present_count'] ?></span>
                                    </td>
                                    <td class="px-4 py-4 last:rounded-r-2xl text-right">
                                        <a href="view_attendance_list.php?class_id=<?= $row['class_id'] ?>&date=<?= $row['date'] ?>" class="w-9 h-9 bg-slate-900 text-white rounded-lg inline-flex items-center justify-center hover:bg-indigo-600 transition-all">
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-20 text-center opacity-30">
                                    <i class="fas fa-folder-open text-4xl mb-3"></i>
                                    <p class="text-[10px] font-black uppercase tracking-widest">No Records Found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.2)]">
        <a href="index.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Home</span>
        </a>
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