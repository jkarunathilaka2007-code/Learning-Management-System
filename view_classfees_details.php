<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$class_id = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$filter_date = isset($_GET['date']) ? mysqli_real_escape_string($conn, $_GET['date']) : '';
$filter_month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : '';

if (empty($class_id)) {
    die("Invalid Class ID.");
}

$class_info = $conn->query("SELECT * FROM classes WHERE class_id = '$class_id'")->fetch_assoc();

$sql = "SELECT p.*, s.full_name, s.id as student_ref 
        FROM class_fees_payments p 
        JOIN student s ON p.student_id = s.id 
        WHERE p.class_id = '$class_id'";

if (!empty($filter_date)) {
    $sql .= " AND DATE(p.payment_date) = '$filter_date'";
} elseif (!empty($filter_month)) {
    $sql .= " AND p.payment_month = '$filter_month'";
}

$sql .= " ORDER BY p.payment_date DESC";
$result = $conn->query($sql);

$total_collected = 0;
$count = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Payment Details - LMS Pro</title>
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
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid #e2e8f0; }
        
        /* Row Visibility Improvements */
        .payment-row { 
            background: white; 
            border: 1px solid #cbd5e1 !important; /* දාරය වඩාත් තද කළා */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); /* සෙවනැල්ලක් එකතු කළා */
        }
        
        .search-input { 
            padding: 14px 20px 14px 45px; border-radius: 15px; border: 2px solid #e2e8f0; 
            outline: none; font-size: 13px; font-weight: 600; background: white;
            width: 100%; transition: 0.3s;
        }
        .search-input:focus { border-color: #4f46e5; }
        .main-table { table-layout: fixed; width: 100%; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen pb-28">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3.5rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="class_fees.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <div class="text-right">
                    <span class="block text-[9px] font-black text-indigo-400 uppercase tracking-widest">Gross Total</span>
                    <span id="summary_total_top" class="text-xl font-black italic">RS. 0</span>
                </div>
            </div>
            
            <h1 class="text-2xl font-black italic tracking-tighter uppercase leading-none truncate">
                <?= $class_info['exam_year'] ?> <?= $class_info['stream'] ?>
            </h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2 italic">
                <?= $class_info['subject'] ?> | Individual Records
            </p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-5 md:p-8">
            
            <div class="relative mb-8">
                <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" id="studentSearch" onkeyup="searchStudent()" placeholder="Search Student Name or ID..." class="search-input shadow-sm">
            </div>

            <div class="overflow-hidden">
                <table class="main-table border-separate border-spacing-y-4" id="paymentTable">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                            <th class="px-2 py-2 w-[55%]">Student</th>
                            <th class="px-2 py-2 text-center w-[15%]">Month</th>
                            <th class="px-2 py-2 text-right w-[30%]">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): 
                                $total_collected += $row['amount'];
                                $count++;
                            ?>
                                <tr class="payment-row transition-all rounded-2xl">
                                    <td class="px-4 py-5 first:rounded-l-2xl border-y border-l border-slate-200">
                                        <h3 class="text-[12px] font-black text-slate-800 uppercase italic truncate student-name">
                                            <?= $row['full_name'] ?>
                                        </h3>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider student-id">ID: <?= $row['student_ref'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-5 text-center border-y border-slate-200">
                                        <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg uppercase tracking-tighter">
                                            <?= substr($row['payment_month'], 0, 3) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-5 last:rounded-r-2xl text-right border-y border-r border-slate-200">
                                        <span class="text-[12px] font-black text-emerald-600 italic">
                                            RS.<?= number_format($row['amount'], 0) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr id="noData"><td colspan="3" class="py-20 text-center opacity-30 text-[10px] font-black uppercase">No records found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 p-5 bg-slate-50 rounded-2xl flex justify-between items-center border border-slate-100">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Students</p>
                    <p class="text-xl font-black text-slate-800" id="record_count"><?= $count ?></p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total Collected</p>
                    <p class="text-xl font-black text-emerald-600 italic" id="summary_total_bottom">RS. <?= number_format($total_collected, 0) ?></p>
                </div>
            </div>
        </div>
    </main>

    <script>
        function searchStudent() {
            let input = document.getElementById('studentSearch').value.toUpperCase();
            let table = document.getElementById('tableBody');
            let rows = table.getElementsByClassName('payment-row');
            let recordCount = 0;
            
            for (let i = 0; i < rows.length; i++) {
                let name = rows[i].getElementsByClassName('student-name')[0].innerText;
                let id = rows[i].getElementsByClassName('student-id')[0].innerText;
                
                if (name.toUpperCase().indexOf(input) > -1 || id.toUpperCase().indexOf(input) > -1) {
                    rows[i].style.display = "";
                    recordCount++;
                } else {
                    rows[i].style.display = "none";
                }
            }
            document.getElementById('record_count').innerText = recordCount;
        }
        document.getElementById('summary_total_top').innerText = 'RS. <?= number_format($total_collected, 0) ?>';
    </script>

    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-2xl">
        <a href="index.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Home</span>
        </a>
        <a href="class_fees.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-wallet text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Fees</span>
        </a>
        <a href="logout.php" class="text-rose-500 flex flex-col items-center">
            <i class="fas fa-power-off text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

</body>
</html>