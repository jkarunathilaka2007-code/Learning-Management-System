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
 * 1. ශිෂ්‍යයා ලියාපදිංචි වී ඇති පන්තිවල PDF පමණක් ලබා ගනී.
 * 2. අලුතින්ම අප්ලෝඩ් කළ ඒවා උඩට පෙන්වයි.
 */
$sql = "SELECT p.*, c.subject, c.exam_year 
        FROM pdf_files p
        JOIN classes c ON p.class_id = c.class_id 
        JOIN student_classes sc ON p.class_id = sc.class_id 
        WHERE sc.student_id = '$student_id'";

if (!empty($search_query)) {
    $sql .= " AND (p.pdf_title LIKE '%$search_query%' OR p.sub_name LIKE '%$search_query%')";
}

$sql .= " ORDER BY p.uploaded_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutes & Resources - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-header { background: #1e293b; border-bottom: 4px solid #10b981; }
        .table-container { background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 60px; background: #0f172a; display: flex; justify-content: space-around; align-items: center; z-index: 1000; }
        tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body class="pb-24">

    <header class="glass-header pt-8 pb-12 px-5 text-white">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Study Materials</h1>
                <p class="text-emerald-400 text-[10px] font-bold uppercase tracking-widest">Tutes, Handouts & Short Notes</p>
            </div>
            <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-book-open text-white"></i>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 -mt-8">
        
        <div class="bg-white p-3 rounded-2xl shadow-lg mb-6 border border-slate-200">
            <form action="" method="GET" class="relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="<?= $search_query ?>" placeholder="Search tutes by name or topic..." 
                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:ring-2 ring-emerald-500/20">
            </form>
        </div>

        <div class="table-container border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-800 text-white text-[10px] font-black uppercase tracking-widest">
                            <th class="px-4 py-4">Topic / Subject</th>
                            <th class="px-4 py-4">Title</th>
                            <th class="px-4 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="border-b border-slate-100 hover:bg-emerald-50/50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="text-[10px] font-black text-emerald-600 uppercase mb-0.5">
                                            <?= $row['sub_name'] ?>
                                        </div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase">
                                            <?= $row['exam_year'] ?> - <?= $row['subject'] ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-[12px] font-extrabold uppercase text-slate-800 leading-tight line-clamp-1">
                                            <?= $row['pdf_title'] ?>
                                        </div>
                                        <div class="text-[8px] text-slate-400 mt-1">
                                            <i class="far fa-clock mr-1"></i> <?= date('M d, Y', strtotime($row['uploaded_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= $row['file_path'] ?>" target="_blank" class="w-9 h-9 flex items-center justify-center bg-slate-100 text-slate-600 rounded-lg hover:bg-emerald-100 hover:text-emerald-600 transition-all">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <a href="<?= $row['file_path'] ?>" download class="w-9 h-9 flex items-center justify-center bg-slate-900 text-white rounded-lg hover:bg-emerald-600 transition-all">
                                                <i class="fas fa-download text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-[10px] font-black uppercase text-slate-400 italic">
                                    <i class="fas fa-folder-open block text-2xl mb-2 opacity-20"></i>
                                    No tutes available yet.
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