<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Grade එක සහ Absent (-1) තත්ත්වය හඳුනාගන්නා Function එක
function getGrade($p, $marks = 0) {
    if ($marks == -1) return ['G' => 'AB', 'C' => 'text-rose-500', 'BG' => 'bg-rose-500']; // Absent
    if ($p >= 95) return ['G' => 'A+', 'C' => 'text-emerald-600', 'BG' => 'bg-emerald-600'];
    if ($p >= 75) return ['G' => 'A', 'C' => 'text-emerald-500', 'BG' => 'bg-emerald-500'];
    if ($p >= 65) return ['G' => 'B', 'C' => 'text-blue-500', 'BG' => 'bg-blue-500'];
    if ($p >= 50) return ['G' => 'C', 'C' => 'text-amber-500', 'BG' => 'bg-amber-500'];
    if ($p >= 35) return ['G' => 'S', 'C' => 'text-orange-500', 'BG' => 'bg-orange-500'];
    return ['G' => 'F', 'C' => 'text-rose-500', 'BG' => 'bg-rose-500'];
}

$teacher_id = $_SESSION['user_id'];
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : '';
$selected_paper = isset($_GET['paper_id']) ? $_GET['paper_id'] : '';

// පන්ති ලැයිස්තුව
$classes = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");

// තෝරාගත් පන්තියට අදාළ පේපර් ලැයිස්තුව
$papers = [];
if ($selected_class) {
    $papers = $conn->query("SELECT * FROM exam_papers WHERE class_id = '$selected_class' ORDER BY paper_id DESC");
}

$report_data = [];
$stats = ['avg' => 0, 'max' => 0, 'total' => 0];
// AB (Absent) ඇතුළුව Grades ගණනය කිරීම සඳහා array එක
$grade_counts = ['A+' => 0, 'A' => 0, 'B' => 0, 'C' => 0, 'S' => 0, 'F' => 0, 'AB' => 0];

if ($selected_paper) {
    // Rank List එක ලබා ගැනීම (Absent අය පහළට යන ලෙස)
    $sql = "SELECT s.id, s.full_name, m.marks_obtained, m.percentage 
            FROM student_marks m
            JOIN student s ON m.student_id = s.id
            WHERE m.paper_id = '$selected_paper'
            ORDER BY m.percentage DESC, m.marks_obtained DESC";
    $report_data = $conn->query($sql);
    
    // Average සහ Max ගණනය කිරීම (Absent වූ අයව Average එකට ගණන් ගන්නේ නැත)
    $stat_sql = "SELECT AVG(percentage) as avg_p, MAX(percentage) as max_p 
                 FROM student_marks WHERE paper_id = '$selected_paper' AND marks_obtained >= 0";
    $stat_res = $conn->query($stat_sql)->fetch_assoc();
    
    // මුළු සිසුන් ගණන
    $total_sql = "SELECT COUNT(mark_id) as total FROM student_marks WHERE paper_id = '$selected_paper'";
    $total_res = $conn->query($total_sql)->fetch_assoc();

    $stats['avg'] = round($stat_res['avg_p'] ?? 0, 1);
    $stats['max'] = round($stat_res['max_p'] ?? 0, 1);
    $stats['total'] = $total_res['total'] ?? 0;

    // Grade Quantities ගණනය කිරීම
    $all_m = $conn->query("SELECT percentage, marks_obtained FROM student_marks WHERE paper_id = '$selected_paper'");
    while($m = $all_m->fetch_assoc()) {
        $g = getGrade($m['percentage'], $m['marks_obtained']);
        $grade_counts[$g['G']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Report - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-header { background: #0f172a; border-radius: 0 0 3.5rem 3.5rem; }
        .card { background: white; border-radius: 2rem; border: 1px solid #f1f5f9; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-24">

    <header class="glass-header text-white pt-12 pb-28 px-6 shadow-2xl">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="admin_page.php" class="bg-white/10 p-3 rounded-2xl hover:bg-white/20 transition-all"><i class="fas fa-arrow-left"></i></a>
            <div class="text-center">
                <h1 class="text-2xl font-black uppercase italic tracking-tighter leading-none">Result<br>Analytics</h1>
            </div>
            <div class="w-10"></div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 -mt-20">
        
        <div class="flex justify-between items-end px-2 mb-3">
            <h2 class="text-xs font-black text-white/90 uppercase tracking-widest drop-shadow-sm ml-2">Report Filters</h2>
            <a href="add_marks.php" class="bg-indigo-500 hover:bg-indigo-600 text-white px-5 py-2.5 rounded-[1.2rem] text-[10px] font-black uppercase tracking-widest shadow-xl shadow-indigo-500/30 transition-all flex items-center gap-2 active:scale-95 border border-indigo-400/50">
                <i class="fas fa-plus"></i> New Exam
            </a>
        </div>

        <div class="card p-6 mb-6">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-1 tracking-widest">Target Class</label>
                    <select name="class_id" onchange="this.form.submit()" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold outline-none focus:border-indigo-500">
                        <option value="">Select Class</option>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= $selected_class == $c['class_id'] ? 'selected' : '' ?>><?= $c['exam_year'] ?> - <?= $c['subject'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase ml-1 tracking-widest">Exam Paper</label>
                    <select name="paper_id" onchange="this.form.submit()" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold outline-none focus:border-indigo-500 disabled:opacity-50" <?= !$selected_class ? 'disabled' : '' ?>>
                        <option value="">Select Paper</option>
                        <?php foreach($papers as $p): ?>
                            <option value="<?= $p['paper_id'] ?>" <?= $selected_paper == $p['paper_id'] ? 'selected' : '' ?>><?= $p['paper_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selected_paper && $stats['total'] > 0): ?>
            
            <div class="flex justify-between items-center mb-4 px-2">
                <h3 class="text-sm font-black text-slate-800 uppercase italic">Exam Dashboard</h3>
                
            </div>

            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="bg-indigo-600 p-4 rounded-[1.5rem] text-white text-center shadow-lg shadow-indigo-100">
                    <p class="text-[8px] font-black uppercase opacity-70 tracking-widest">Avg. Score</p>
                    <h3 class="text-xl font-black mt-1"><?= $stats['avg'] ?>%</h3>
                </div>
                <div class="bg-emerald-500 p-4 rounded-[1.5rem] text-white text-center shadow-lg shadow-emerald-100">
                    <p class="text-[8px] font-black uppercase opacity-70 tracking-widest">Highest</p>
                    <h3 class="text-xl font-black mt-1"><?= $stats['max'] ?>%</h3>
                </div>
                <div class="bg-slate-800 p-4 rounded-[1.5rem] text-white text-center shadow-lg shadow-slate-200">
                    <p class="text-[8px] font-black uppercase opacity-70 tracking-widest">Students</p>
                    <h3 class="text-xl font-black mt-1"><?= $stats['total'] ?></h3>
                </div>
            </div>

            <div class="card p-5 mb-6">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 text-center border-b border-slate-100 pb-3">Grade Distribution</h4>
                <div class="flex flex-wrap justify-center gap-2">
                    <?php 
                    $grades = ['A+', 'A', 'B', 'C', 'S', 'F', 'AB']; // AB එකත් ඇතුළත් කර ඇත
                    foreach($grades as $g_key): 
                        if($grade_counts[$g_key] == 0) continue; // 0 ඒවා පෙන්වන්නේ නැත
                        
                        $bg_color = ($g_key=='A+')?'bg-emerald-600':(($g_key=='A')?'bg-emerald-500':(($g_key=='B')?'bg-blue-500':(($g_key=='C')?'bg-amber-500':(($g_key=='S')?'bg-orange-500':(($g_key=='AB')?'bg-rose-600':'bg-rose-500')))));
                    ?>
                    <div class="<?= $bg_color ?> text-white px-3 py-2 rounded-xl flex items-center gap-2 shadow-sm">
                        <span class="font-black text-xs"><?= $g_key ?></span>
                        <span class="bg-white/20 px-2 py-0.5 rounded-lg font-bold text-[10px]"><?= $grade_counts[$g_key] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="p-5 bg-slate-50/50 flex justify-between border-b border-slate-100">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Leaderboard</span>
                    <a href="edit_paper.php?paper_id=<?= $selected_paper ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-[1rem] text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-200 transition-all flex items-center gap-2">
                    <i class="fas fa-pen"></i> Edit Marks
                </a>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</span>
                </div>
                <div class="divide-y divide-slate-50">
                    <?php 
                    $rank = 1;
                    while($row = $report_data->fetch_assoc()): 
                        $is_absent = ($row['marks_obtained'] == -1);
                        $g_res = getGrade($row['percentage'], $row['marks_obtained']);
                        
                        // Absent නම් රෑන්ක් එක වෙනුවට '-' පෙන්වයි
                        $display_rank = $is_absent ? '<i class="fas fa-user-times"></i>' : $rank;
                    ?>
                    <div class="p-5 flex items-center justify-between hover:bg-slate-50 transition-all <?= $is_absent ? 'opacity-70' : '' ?>">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-xs <?= (!$is_absent && $rank <= 3) ? 'bg-indigo-100 text-indigo-600 shadow-inner' : 'bg-slate-100 text-slate-400' ?>">
                                <?= $display_rank ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-slate-700 uppercase leading-none <?= $is_absent ? 'line-through text-slate-400' : '' ?>"><?= $row['full_name'] ?></h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-slate-400 tracking-tighter">ID: <?= $row['id'] ?></span>
                                    <?php if($is_absent): ?>
                                        <span class="text-[10px] font-black text-rose-500 uppercase bg-rose-50 px-2 py-0.5 rounded-md">Absent</span>
                                    <?php else: ?>
                                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                                        <span class="text-[10px] font-black text-indigo-500 uppercase"><?= round($row['marks_obtained']) ?> Marks</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-black <?= $g_res['C'] ?> leading-none"><?= $g_res['G'] ?></p>
                            <?php if(!$is_absent): ?>
                                <p class="text-[9px] font-bold text-slate-300 uppercase mt-1 tracking-widest"><?= round($row['percentage']) ?>%</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                        if(!$is_absent) $rank++; // Absent නැති අයට පමණක් Rank එක වැඩි වේ
                    endwhile; 
                    ?>
                </div>
            </div>

        <?php elseif($selected_paper): ?>
            <div class="card p-20 text-center opacity-40">
                <i class="fas fa-box-open text-5xl mb-4"></i>
                <p class="text-[10px] font-black uppercase tracking-[0.2em]">No results for this paper</p>
            </div>
        <?php else: ?>
            <div class="text-center py-24 opacity-30">
                <i class="fas fa-chart-line text-7xl mb-6 text-slate-400"></i>
                <p class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-500">Select Exam Paper<br>To View Analytics</p>
            </div>
        <?php endif; ?>
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