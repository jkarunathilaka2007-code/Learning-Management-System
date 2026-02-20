<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Filter අගයන් ලබා ගැනීම
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$filter_date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';
$filter_month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : '';
$filter_year = date('Y');

// Query එක සකස් කිරීම
$sql = "SELECT c.class_id, c.subject, c.exam_year, c.stream, c.fee_type, 
               COUNT(p.id) as student_qty, SUM(p.amount) as total_amount 
        FROM classes c 
        LEFT JOIN class_fees_payments p ON c.class_id = p.class_id";

$where = [];
if (!empty($filter_class)) $where[] = "c.class_id = '$filter_class'";
if (!empty($filter_date)) {
    $where[] = "DATE(p.payment_date) = '$filter_date'";
} elseif (!empty($filter_month)) {
    $where[] = "p.payment_month = '$filter_month' AND YEAR(p.payment_date) = '$filter_year'";
}

if (count($where) > 0) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " GROUP BY c.class_id HAVING student_qty > 0 ORDER BY c.class_id DESC";
$result = $conn->query($sql);
$classes_list = $conn->query("SELECT * FROM classes ORDER BY exam_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Finance Records - LMS Pro</title>
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
        .main-table { table-layout: fixed; width: 100%; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen pb-28">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto text-center md:text-left">
            <div class="flex justify-between items-center mb-6">
                <a href="admin_page.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <a href="unpaid_students.php" class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg active:scale-90 transition-all">
                    <i class="fas fa-cancel text-white text-sm"></i>
                </a>
                <a href="mark_classfees.php" class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg active:scale-90 transition-all">
                    <i class="fas fa-plus text-white text-sm"></i>
                </a>
            </div>
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Class Fees</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Financial Records & Analytics</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-4 md:p-8">
            
            <form action="" method="GET" class="space-y-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <select name="class_id" class="filter-input" onchange="this.form.submit()">
                        <option value="">All Classes</option>
                        <?php 
                        $classes_list->data_seek(0);
                        while($c = $classes_list->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($filter_class == $c['class_id']) ? 'selected' : '' ?>>
                                <?= $c['exam_year'] ?> <?= $c['stream'] ?> <?= $c['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="date" name="date" class="filter-input" value="<?= $filter_date ?>" onchange="document.getElementsByName('month')[0].value=''; this.form.submit()">
                    <select name="month" class="filter-input" onchange="document.getElementsByName('date')[0].value=''; this.form.submit()">
                        <option value="">Select Month</option>
                        <?php 
                        $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                        foreach($months as $m) echo "<option value='$m' ".($filter_month==$m?'selected':'').">$m</option>";
                        ?>
                    </select>
                </div>
            </form>

            <div class="overflow-hidden">
                <table class="main-table border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-[9px] uppercase font-black text-slate-400 tracking-widest">
                            <th class="px-2 py-2 w-[50%]">Class</th>
                            <th class="px-2 py-2 text-center w-[15%]">Qty</th>
                            <th class="px-2 py-2 text-right w-[35%]">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="bg-white hover:bg-slate-50 transition-all rounded-2xl group shadow-sm border border-slate-100">
                                    <td class="px-3 py-4 first:rounded-l-2xl">
                                        <h3 class="text-[11px] font-black text-slate-800 uppercase italic truncate">
                                            <?= $row['exam_year'] ?> <?= $row['stream'] ?>
                                        </h3>
                                        <p class="text-[8px] font-bold text-indigo-500 uppercase truncate"><?= $row['subject'] ?></p>
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        <span class="text-[11px] font-bold text-slate-600"><?= $row['student_qty'] ?></span>
                                    </td>
                                    <td class="px-3 py-4 last:rounded-r-2xl text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <span class="text-[10px] font-black text-emerald-600 italic">RS.<?= number_format($row['total_amount'], 0) ?></span>
                                            <a href="view_classfees_details.php?class_id=<?= $row['class_id'] ?>&date=<?= $filter_date ?>&month=<?= $filter_month ?>" 
                                               class="w-8 h-8 bg-slate-900 text-white rounded-lg flex items-center justify-center hover:bg-indigo-600 transition-all shadow-md">
                                                <i class="fas fa-eye text-[10px]"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="py-20 text-center opacity-30 text-[10px] font-black uppercase">No Data Found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-2xl">
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