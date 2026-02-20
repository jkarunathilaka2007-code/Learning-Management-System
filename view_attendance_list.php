<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$class_id = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';

if (empty($class_id) || empty($date)) {
    header("Location: attendance.php");
    exit();
}

$class_info = $conn->query("SELECT * FROM classes WHERE class_id = '$class_id'")->fetch_assoc();

$sql = "SELECT s.id, s.full_name, s.contact_number, a.status, a.created_at 
        FROM attendance a 
        JOIN student s ON a.student_id = s.id 
        WHERE a.class_id = '$class_id' AND a.date = '$date'
        ORDER BY a.created_at ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Details - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border-radius: 2rem; border: 1px solid white; }
        .search-box { position: sticky; top: 1rem; z-index: 10; }
    </style>
</head>
<body class="min-h-screen pb-20">

    <div class="bg-slate-900 text-white pb-20 pt-8 px-6 rounded-b-[3rem] shadow-2xl">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="attendance.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <span class="bg-emerald-500/20 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest text-emerald-400">
                    Verified
                </span>
            </div>
            
            <h1 class="text-2xl font-black italic tracking-tighter uppercase leading-tight">
                <?= $class_info['exam_year'] ?> <?= $class_info['stream'] ?>
            </h1>
            <p class="text-indigo-400 text-[11px] font-bold uppercase tracking-widest mt-1">
                <?= $class_info['subject'] ?> • <?= date('M d, Y', strtotime($date)) ?>
            </p>
        </div>
    </div>

    <main class="max-w-4xl mx-auto px-4 -mt-10">
        <div class="search-box mb-6">
            <div class="relative group">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-500"></i>
                <input type="text" id="studentSearch" class="w-full p-4 pl-12 bg-white shadow-xl rounded-2xl border border-slate-100 outline-none focus:ring-4 ring-indigo-500/10 font-bold text-sm" placeholder="Search ID or Name...">
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 flex justify-between items-center">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Students</span>
            <span class="bg-slate-900 text-white px-3 py-1 rounded-lg text-xs font-black"><?= $result->num_rows ?></span>
        </div>

        <div id="studentContainer" class="space-y-3">
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="student-card glass-card p-4 flex items-center justify-between group hover:border-indigo-200 transition-all shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all font-black text-sm">
                                <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                            </div>
                            
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase italic leading-none student-name">
                                    <?= $row['full_name'] ?>
                                </h3>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest student-id">ID: <?= $row['id'] ?></span>
                                    <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                    <span class="text-[9px] font-bold text-indigo-500 uppercase tracking-widest"><?= date('h:i A', strtotime($row['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="py-20 text-center opacity-30">
                    <i class="fas fa-user-clock text-5xl mb-4"></i>
                    <p class="text-xs font-black uppercase tracking-widest">No Attendance Data</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="noResults" class="hidden py-20 text-center">
            <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest">No matching students found</p>
        </div>
    </main>

    <script>
    $(document).ready(function(){
        $("#studentSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var count = 0;

            $("#studentContainer .student-card").filter(function() {
                var name = $(this).find(".student-name").text().toLowerCase();
                var id = $(this).find(".student-id").text().toLowerCase();
                var isMatch = name.indexOf(value) > -1 || id.indexOf(value) > -1;
                
                $(this).toggle(isMatch);
                if(isMatch) count++;
            });

            if(count === 0 && value !== "") {
                $("#noResults").removeClass("hidden");
            } else {
                $("#noResults").addClass("hidden");
            }
        });
    });
    </script>

</body>
</html>