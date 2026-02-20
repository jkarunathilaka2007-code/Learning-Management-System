<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$filter_year = isset($_GET['year']) ? mysqli_real_escape_string($conn, $_GET['year']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

/**
 * SQL Query:
 * 1. Year එක අනුව අලුත් ඒවා උඩට (DESC)
 * 2. එකම අවුරුද්දේ ඒවා Title එක අනුව A-Z (ASC)
 */
$sql = "SELECT * FROM past_papers WHERE 1=1";
if (!empty($filter_year)) $sql .= " AND year = '$filter_year'";
if (!empty($search_query)) $sql .= " AND (title LIKE '%$search_query%' OR category LIKE '%$search_query%')";
$sql .= " ORDER BY year DESC, title ASC";

$result = $conn->query($sql);
$years_list = $conn->query("SELECT DISTINCT year FROM past_papers ORDER BY year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Papers - Table View</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-header { background: #0f172a; border-bottom: 4px solid #4f46e5; }
        .table-container { background: white; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 60px; background: #0f172a; display: flex; justify-content: space-around; align-items: center; z-index: 1000; }
        /* පේළි මාරුවෙන් මාරුවට පාට කිරීම */
        tr:nth-child(even) { background-color: #f1f5f9; }
    </style>
</head>
<body class="pb-24">

    <header class="glass-header pt-8 pb-12 px-5 text-white">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Paper Inventory</h1>
                <p class="text-indigo-400 text-[10px] font-bold uppercase tracking-widest">Year-wise Sorted Collection</p>
            </div>
            <a href="index.php" class="bg-white/10 px-4 py-2 rounded-lg text-[10px] font-bold uppercase">Home</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 -mt-8">
        
        <div class="bg-white p-3 rounded-2xl shadow-lg mb-6 flex flex-col md:flex-row gap-3 border border-slate-200">
            <form action="" method="GET" class="w-full flex flex-col md:flex-row gap-2">
                <input type="text" name="search" value="<?= $search_query ?>" placeholder="Search by title..." class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium outline-none focus:ring-2 ring-indigo-500/20">
                <select name="year" class="md:w-48 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold uppercase">
                    <option value="">All Years</option>
                    <?php while($y = $years_list->fetch_assoc()): ?>
                        <option value="<?= $y['year'] ?>" <?= ($filter_year == $y['year']) ? 'selected' : '' ?>><?= $y['year'] ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest">Apply</button>
            </form>
        </div>

        <div class="table-container border border-slate-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest">
                            <th class="px-4 py-4">Year</th>
                            <th class="px-4 py-4">Paper Title / Category</th>
                            <th class="px-4 py-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700">
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $file_url = "uploads/past_papers/" . $row['paper_file'];
                            ?>
                                <tr class="border-b border-slate-100 hover:bg-indigo-50/50 transition-colors">
                                    <td class="px-4 py-4">
                                        <span class="bg-indigo-600 text-white text-[10px] font-black px-2 py-1 rounded">
                                            <?= $row['year'] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-[12px] font-extrabold uppercase text-slate-800 leading-tight">
                                            <?= $row['title'] ?>
                                        </div>
                                        <div class="text-[9px] font-bold text-slate-400 uppercase mt-1">
                                            <?= $row['category'] ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?= $file_url ?>" target="_blank" class="w-9 h-9 flex items-center justify-center bg-slate-100 text-slate-600 rounded-lg hover:bg-indigo-100 hover:text-indigo-600 transition-all" title="View PDF">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <a href="<?= $file_url ?>" download class="w-9 h-9 flex items-center justify-center bg-slate-900 text-white rounded-lg hover:bg-rose-600 transition-all" title="Download">
                                                <i class="fas fa-download text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-[10px] font-black uppercase text-slate-400 italic">No papers found in current criteria.</td>
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