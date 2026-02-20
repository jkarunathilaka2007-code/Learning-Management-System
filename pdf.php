<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Filter values
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';

// Base SQL Query - PDF list eka ganna (Classes table ekath ekka join karala)
$sql = "SELECT p.*, c.subject, c.exam_year, c.stream 
        FROM pdf_files p 
        JOIN classes c ON p.class_id = c.class_id 
        WHERE c.teacher_id = '$teacher_id'";

if (!empty($filter_class)) {
    $sql .= " AND p.class_id = '$filter_class'";
}

$sql .= " ORDER BY p.uploaded_at DESC";
$result = $conn->query($sql);

// Dropdown eka sandaha class list eka
$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Library - LMS Pro</title>
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
        .filter-select { 
            padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; 
            outline: none; font-size: 13px; font-weight: 700; background: white;
            width: 100%; transition: 0.3s;
        }
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
            
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">PDF Library</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Manage Study Materials</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-5 md:p-8">
            
            <form action="" method="GET" class="flex gap-3 mb-8 items-end">
                <div class="flex-1">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Filter by Class</label>
                    <select name="class_id" class="filter-select">
                        <option value="">All Materials</option>
                        <?php while($c = $classes_list->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($filter_class == $c['class_id']) ? 'selected' : '' ?>>
                                <?= $c['exam_year'] ?> <?= $c['stream'] ?> <?= $c['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all">
                    Filter
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest">
                            <th class="px-4 py-2">Document</th>
                            <th class="px-4 py-2">Class Info</th>
                            <th class="px-4 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="bg-white hover:bg-slate-50 transition-all rounded-2xl group shadow-sm border border-slate-100">
                                    <td class="px-4 py-4 first:rounded-l-2xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-lg flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-all shrink-0">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-xs font-black text-slate-800 uppercase italic truncate max-w-[150px] md:max-w-none">
                                                    <?= $row['pdf_title'] ?>
                                                </h3>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5"><?= $row['sub_name'] ?: 'No Description' ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="text-[10px] font-black text-slate-700 uppercase italic leading-none"><?= $row['exam_year'] ?> <?= $row['stream'] ?></p>
                                        <span class="text-[8px] font-bold text-indigo-500 uppercase tracking-[0.2em]"><?= $row['subject'] ?></span>
                                    </td>
                                    <td class="px-4 py-4 last:rounded-r-2xl text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="<?= $row['file_path'] ?>" target="_blank" class="w-9 h-9 bg-slate-100 text-slate-600 rounded-lg inline-flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all">
                                                <i class="fas fa-eye text-[10px]"></i>
                                            </a>
                                            <a href="delete_pdf.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="w-9 h-9 bg-rose-50 text-rose-500 rounded-lg inline-flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all">
                                                <i class="fas fa-trash text-[10px]"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="py-20 text-center opacity-30">
                                    <i class="fas fa-file-circle-exclamation text-4xl mb-3"></i>
                                    <p class="text-[10px] font-black uppercase tracking-widest">No PDF Documents Found</p>
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