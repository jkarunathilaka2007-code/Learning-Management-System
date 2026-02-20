<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// --- STATISTICS CALCULATION ---
$stats_sql = "SELECT sm.percentage FROM student_marks sm 
              JOIN exam_papers ep ON sm.paper_id = ep.paper_id 
              JOIN student_classes sc ON ep.class_id = sc.class_id 
              WHERE sm.student_id = '$student_id' AND sc.student_id = '$student_id'";
$stats_res = $conn->query($stats_sql);

$total_papers = 0; $total_perc_sum = 0;
$count_a = 0; $count_b = 0; $count_c = 0; $count_s = 0; $count_f = 0;

if ($stats_res->num_rows > 0) {
    $total_papers = $stats_res->num_rows;
    while($st_row = $stats_res->fetch_assoc()) {
        $p = $st_row['percentage'];
        $total_perc_sum += $p;
        if($p >= 75) $count_a++;
        elseif($p >= 65) $count_b++;
        elseif($p >= 50) $count_c++;
        elseif($p >= 35) $count_s++;
        else $count_f++;
    }
    $avg_score = round($total_perc_sum / $total_papers, 1);
} else { $avg_score = 0; }

// --- FILTER & SEARCH ---
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$sql = "SELECT sm.*, ep.paper_name, ep.max_marks, c.subject FROM student_marks sm 
        JOIN exam_papers ep ON sm.paper_id = ep.paper_id 
        JOIN classes c ON ep.class_id = c.class_id 
        JOIN student_classes sc ON ep.class_id = sc.class_id 
        WHERE sm.student_id = '$student_id' AND sc.student_id = '$student_id'";

if (!empty($filter_class)) $sql .= " AND c.class_id = '$filter_class'";
if (!empty($search_query)) $sql .= " AND ep.paper_name LIKE '%$search_query%'";

$sql .= " ORDER BY sm.added_at DESC";
$result = $conn->query($sql);
$my_classes = $conn->query("SELECT c.class_id, c.subject FROM classes c JOIN student_classes sc ON c.class_id = sc.class_id WHERE sc.student_id = '$student_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Dashboard | LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border: 1px solid white; }
        #rankModal { display: none; }
        .modal-active { display: flex !important; animation: zoomIn 0.2s ease-out; }
        @keyframes zoomIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-10">

    <div id="rankModal" class="fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
        <div class="bg-white w-full max-w-xs rounded-3xl p-6 shadow-2xl relative">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-rose-500"><i class="fas fa-times-circle text-xl"></i></button>
            <h2 class="text-center text-sm font-black uppercase italic mb-4">Top 3 <span class="text-indigo-600">Students</span></h2>
            <div id="rankList" class="space-y-2 text-xs"></div>
        </div>
    </div>

    <div class="bg-slate-900 text-white pt-8 pb-32 px-4 rounded-b-[2.5rem] shadow-xl relative overflow-hidden">
        <div class="max-w-6xl mx-auto relative z-10">
            <div class="flex justify-between items-center mb-6">
                <a href="student_page.php" class="bg-white/10 p-2 px-4 rounded-lg text-[10px] font-black uppercase tracking-widest"><i class="fas fa-arrow-left mr-2"></i>Back</a>
                <div class="text-right">
                    <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest leading-none">Total Average</p>
                    <h2 class="text-3xl font-black italic text-white leading-none mt-1"><?= $avg_score ?>%</h2>
                </div>
            </div>

            <div class="flex md:grid md:grid-cols-6 gap-3 overflow-x-auto no-scrollbar pb-2">
                <div class="bg-white/10 border border-white/5 p-3 rounded-2xl text-center min-w-[85px] flex-1">
                    <p class="text-[8px] font-black text-slate-400 uppercase">Total Papers</p>
                    <span class="text-lg font-black italic"><?= $total_papers ?></span>
                </div>
                <div class="bg-emerald-500/20 border border-emerald-500/20 p-3 rounded-2xl text-center min-w-[65px] flex-1">
                    <p class="text-[8px] font-black text-emerald-400 uppercase">A</p>
                    <span class="text-lg font-black italic text-emerald-400"><?= $count_a ?></span>
                </div>
                <div class="bg-blue-500/20 border border-blue-500/20 p-3 rounded-2xl text-center min-w-[65px] flex-1">
                    <p class="text-[8px] font-black text-blue-400 uppercase">B</p>
                    <span class="text-lg font-black italic text-blue-400"><?= $count_b ?></span>
                </div>
                <div class="bg-amber-500/20 border border-amber-500/20 p-3 rounded-2xl text-center min-w-[65px] flex-1">
                    <p class="text-[8px] font-black text-amber-400 uppercase">C</p>
                    <span class="text-lg font-black italic text-amber-400"><?= $count_c ?></span>
                </div>
                <div class="bg-slate-500/20 border border-slate-500/20 p-3 rounded-2xl text-center min-w-[65px] flex-1">
                    <p class="text-[8px] font-black text-slate-400 uppercase">S</p>
                    <span class="text-lg font-black italic text-slate-300"><?= $count_s ?></span>
                </div>
                <div class="bg-rose-500/20 border border-rose-500/20 p-3 rounded-2xl text-center min-w-[65px] flex-1">
                    <p class="text-[8px] font-black text-rose-400 uppercase">F</p>
                    <span class="text-lg font-black italic text-rose-400"><?= $count_f ?></span>
                </div>
            </div>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 -mt-14 relative z-20">
        <form action="" method="GET" class="glass shadow-lg p-3 mb-6 rounded-2xl border border-white flex flex-col md:flex-row gap-2">
            <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Search paper..." class="flex-1 px-4 py-2.5 rounded-xl border border-slate-100 text-xs font-bold outline-none focus:ring-2 ring-indigo-50 transition-all">
            <select name="class_id" class="px-4 py-2.5 rounded-xl border border-slate-100 text-xs font-bold outline-none bg-white md:w-48">
                <option value="">All Subjects</option>
                <?php while($c = $my_classes->fetch_assoc()): ?>
                    <option value="<?= $c['class_id'] ?>" <?= ($filter_class == $c['class_id']) ? 'selected' : '' ?>><?= $c['subject'] ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600">Filter</button>
        </form>

        <div class="glass shadow-xl rounded-3xl overflow-hidden border border-white">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-100">
                    <tr class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-4 md:px-8 py-4">Paper Name</th>
                        <th class="px-2 py-4 text-center">Score</th>
                        <th class="px-2 py-4 text-center">Grade</th>
                        <th class="px-4 py-4 text-right">Insight</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <?php 
                                $p = $row['percentage'];
                                if($p >= 75) { $g = 'A'; $cl = 'text-emerald-600'; $bg = 'bg-emerald-50'; }
                                elseif($p >= 65) { $g = 'B'; $cl = 'text-blue-600'; $bg = 'bg-blue-50'; }
                                elseif($p >= 50) { $g = 'C'; $cl = 'text-amber-600'; $bg = 'bg-amber-50'; }
                                elseif($p >= 35) { $g = 'S'; $cl = 'text-slate-600'; $bg = 'bg-slate-50'; }
                                else { $g = 'F'; $cl = 'text-rose-600'; $bg = 'bg-rose-50'; }
                                if($p <= 0) { $g = 'ABS'; $cl = 'text-rose-400'; $bg = 'bg-rose-50'; }
                            ?>
                            <tr class="bg-white/50 hover:bg-white transition-colors">
                                <td class="px-4 md:px-8 py-4">
                                    <h3 class="text-[10px] md:text-xs font-black text-slate-800 uppercase italic leading-tight truncate max-w-[130px] md:max-w-none"><?= $row['paper_name'] ?></h3>
                                    <p class="text-[8px] font-bold text-indigo-500 uppercase mt-1 md:block hidden"><?= $row['subject'] ?></p>
                                </td>
                                <td class="px-2 py-4 text-center">
                                    <span class="text-[11px] md:text-xs font-black text-slate-900 md:inline hidden"><?= $row['marks_obtained'] ?> </span>
                                    <span class="text-[10px] md:text-[11px] font-black text-indigo-600"><?= $p ?>%</span>
                                </td>
                                <td class="px-2 py-4 text-center uppercase">
                                    <span class="<?= $bg ?> <?= $cl ?> text-[9px] font-black px-2 py-1 rounded-md border border-black/5 italic">
                                        <?= $g ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <button onclick="openRanks('<?= $row['paper_id'] ?>', '<?= $row['paper_name'] ?>')" class="w-8 h-8 bg-slate-900 text-white rounded-lg shadow-lg flex items-center justify-center ml-auto hover:bg-indigo-600 transition-all">
                                        <i class="fas fa-chart-line text-[10px]"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="py-16 text-center text-[10px] font-black uppercase opacity-20 italic">No Results Recorded</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

    <script>
        function openRanks(paperId, paperName) {
            $('#rankList').html('<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-indigo-500"></i></div>');
            $('#rankModal').addClass('modal-active');
            $.ajax({
                url: 'get_top_performers.php',
                method: 'POST',
                data: { paper_id: paperId },
                success: function(res) { $('#rankList').html(res); }
            });
        }
        function closeModal() { $('#rankModal').removeClass('modal-active'); }
        $(window).click(function(e) { if (e.target.id == 'rankModal') closeModal(); });
    </script>
</body>
</html>