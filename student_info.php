<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$search_query = "";

if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
}

// Student Delete Logic
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $conn->query("DELETE FROM student WHERE id = '$del_id'");
    header("Location: student_info.php?msg=deleted");
    exit();
}

// දත්ත ලබා ගැනීම
$sql = "SELECT * FROM student WHERE (full_name LIKE '%$search_query%' OR gmail LIKE '%$search_query%') ORDER BY id DESC";
$result = $conn->query($sql);
$total_students = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Directory - LMS Pro</title>
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
        .glass-card { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            border-radius: 1.25rem; 
        }
        /* Mobile scroll hide */
        .no-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="min-h-screen pb-10">

    <div class="bg-slate-900 text-white pb-20 pt-8 px-6 rounded-b-[2.5rem] shadow-xl">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <a href="admin_page.php" class="flex items-center gap-2 bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl transition-all border border-white/10 text-xs font-bold uppercase tracking-widest">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Home
                </a>
                <div class="text-right">
                    <h1 class="text-xl font-black italic tracking-tighter uppercase leading-none">LMS Pro</h1>
                    <span class="text-[9px] font-bold text-slate-400 tracking-[0.2em] uppercase">Student Directory</span>
                    <a href="student_reg.php" class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 active:scale-90">
                    <i class="fas fa-plus text-white"></i>
                </a>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight">Manage Students</h2>
                    <p class="text-slate-400 text-sm mt-1">You have <span class="text-indigo-400 font-bold"><?= $total_students ?></span> registered students</p>
                </div>
                
                <form action="" method="GET" class="relative w-full md:w-80 group">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" 
                           placeholder="Search name or gmail..." 
                           class="w-full bg-slate-800/50 border border-slate-700 rounded-2xl py-3.5 px-5 pl-12 text-sm outline-none focus:ring-2 ring-indigo-500/50 focus:bg-slate-800 transition-all text-white">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors"></i>
                </form>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 -mt-10">
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="bg-emerald-500/10 text-emerald-600 p-4 rounded-2xl mb-6 font-bold text-center border border-emerald-500/20 text-xs animate-bounce">
                <i class="fas fa-check-circle mr-2"></i> Student deleted successfully!
            </div>
        <?php endif; ?>

        <div class="glass-card shadow-xl shadow-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto no-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-5 text-[10px] uppercase font-black text-slate-400 tracking-widest">Basic Info</th>
                            <th class="px-4 py-5 text-[10px] uppercase font-black text-slate-400 tracking-widest hidden sm:table-cell">Contact & Gmail</th>
                            <th class="px-4 py-5 text-[10px] uppercase font-black text-slate-400 tracking-widest text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 md:gap-4">
                                            <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-black text-sm shadow-lg shadow-indigo-200">
                                                <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                                            </div>
                                            <div class="max-w-[120px] md:max-w-none truncate">
                                                <p class="font-bold text-slate-800 text-sm md:text-base truncate"><?= $row['full_name'] ?></p>
                                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight sm:hidden"><?= $row['gmail'] ?></p>
                                                <p class="text-[10px] text-indigo-500 font-black uppercase hidden sm:block">ID: ST-<?= $row['id'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-4 hidden sm:table-cell">
                                        <div class="space-y-0.5">
                                            <p class="text-xs font-semibold text-slate-700 flex items-center gap-2">
                                                <i class="fas fa-envelope text-indigo-300"></i> <?= $row['gmail'] ?>
                                            </p>
                                            <p class="text-xs font-medium text-slate-400 flex items-center gap-2">
                                                <i class="fas fa-phone text-slate-300"></i> <?= $row['contact_number'] ?>
                                            </p>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="view_student.php?id=<?= $row['id'] ?>" class="w-9 h-9 md:w-10 md:h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <a href="student_info.php?delete_id=<?= $row['id'] ?>" 
                                               onclick="return confirm('Remove this student?')" 
                                               class="w-9 h-9 md:w-10 md:h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-user-slash text-2xl text-slate-200"></i>
                                        </div>
                                        <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest">No matching students found</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>