<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$st = $conn->query("SELECT full_name FROM student WHERE id = '$student_id'")->fetch_assoc();

$filter_month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : '';
$filter_year = isset($_GET['year']) ? mysqli_real_escape_string($conn, $_GET['year']) : date('Y');

$sql = "SELECT p.*, c.subject, c.exam_year 
        FROM class_fees_payments p 
        JOIN classes c ON p.class_id = c.class_id 
        WHERE p.student_id = '$student_id'";

if (!empty($filter_month)) { $sql .= " AND p.payment_month = '$filter_month'"; }
if (!empty($filter_year)) { $sql .= " AND YEAR(p.payment_date) = '$filter_year'"; }

$sql .= " ORDER BY p.payment_date DESC";
$result = $conn->query($sql);

$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees History - LMS Pro</title>
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
        
        /* Mobile Table Transformation */
        @media (max-width: 768px) {
            .responsive-table thead { display: none; }
            .responsive-table tr { 
                display: block; 
                background: white; 
                margin-bottom: 1rem; 
                padding: 1rem; 
                border-radius: 1.5rem;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            }
            .responsive-table td { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                padding: 0.5rem 0;
                border: none !important;
            }
            .responsive-table td::before { 
                content: attr(data-label); 
                font-size: 9px; 
                font-weight: 800; 
                text-transform: uppercase; 
                color: #94a3b8;
            }
        }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-10">

    <div class="bg-slate-900 text-white pb-24 pt-8 px-6 rounded-b-[3rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <a href="student_page.php" class="inline-flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest mb-6">
                <i class="fas fa-arrow-left text-indigo-400"></i> Back
            </a>
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Class <span class="text-indigo-500">Fees</span></h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2 italic">Official Payment Records</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-12">
        <div class="glass-card shadow-2xl p-5 mb-6">
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block ml-1">Month</label>
                    <select name="month" class="w-full p-3 rounded-xl border border-slate-100 font-bold text-xs outline-none">
                        <option value="">All Months</option>
                        <?php foreach($months as $m): ?>
                            <option value="<?= $m ?>" <?= ($filter_month == $m) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 block ml-1">Year</label>
                    <select name="year" class="w-full p-3 rounded-xl border border-slate-100 font-bold text-xs outline-none">
                        <?php $cy = date('Y'); for($i=$cy; $i>=$cy-2; $i--) echo "<option value='$i' ".($filter_year == $i ? 'selected' : '').">$i</option>"; ?>
                    </select>
                </div>
                <button type="submit" class="bg-slate-900 text-white py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition-all">Filter</button>
            </form>
        </div>

        <div class="md:glass-card md:shadow-2xl md:p-8">
            <table class="w-full responsive-table border-separate border-spacing-y-2 md:border-spacing-y-4">
                <thead class="hidden md:table-header-group">
                    <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest">
                        <th class="px-6 py-2">Subject / Month</th>
                        <th class="px-6 py-2">Paid Date</th>
                        <th class="px-6 py-2 text-center">Amount</th>
                        <th class="px-6 py-2 text-right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="bg-white md:hover:bg-slate-50 transition-all md:rounded-3xl shadow-sm border border-slate-50">
                                <td class="px-6 py-4 md:py-5 md:rounded-l-[1.5rem]" data-label="Class">
                                    <div class="flex items-center gap-3">
                                        <div class="hidden md:flex w-10 h-10 bg-indigo-600 text-white rounded-xl items-center justify-center font-black text-[10px] uppercase">
                                            <?= substr($row['payment_month'], 0, 3) ?>
                                        </div>
                                        <div class="text-right md:text-left w-full md:w-auto">
                                            <h3 class="text-xs font-black text-slate-800 uppercase italic leading-none"><?= $row['subject'] ?></h3>
                                            <p class="text-[9px] font-bold text-indigo-500 uppercase mt-1"><?= $row['payment_month'] ?> Fee</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 md:py-5" data-label="Paid Date">
                                    <div class="text-right md:text-left">
                                        <p class="text-[11px] font-bold text-slate-700"><?= date('M d, Y', strtotime($row['payment_date'])) ?></p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">ID: #<?= $row['id'] ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 md:py-5 text-center" data-label="Amount">
                                    <span class="text-sm font-black text-slate-900">Rs. <?= number_format($row['amount'], 2) ?></span>
                                </td>
                                <td class="px-6 py-4 md:py-5 md:rounded-r-[1.5rem] text-right" data-label="Status">
                                    <span class="bg-emerald-50 text-emerald-600 text-[9px] font-black px-3 py-1 rounded-full border border-emerald-100">PAID</span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="bg-transparent shadow-none">
                            <td colspan="4" class="py-20 text-center opacity-30 border-none">
                                <i class="fas fa-receipt text-5xl mb-3"></i>
                                <p class="text-[10px] font-black uppercase tracking-widest">No records found</p>
                            </td>
                        </tr>
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

    <div class="text-center mt-8 pb-6">
        <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.5em]">LMS PRO • Verified Payments</p>
    </div>
</body>
</html>