<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Filter values
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$filter_month = isset($_GET['month']) ? mysqli_real_escape_string($conn, $_GET['month']) : date('F');
$filter_year = date('Y');

// Get Classes List
$classes_list = $conn->query("SELECT * FROM classes ORDER BY exam_year DESC");

$unpaid_result = [];

if (!empty($filter_class)) {
    /**
     * ADVANCED LOGIC:
     * 1. JOIN student with student_classes (Registered students)
     * 2. WHERE EXISTS in attendance (Came to class at least once in that month)
     * 3. AND NOT IN class_fees_payments (Hasn't paid for that month)
     */
    $sql = "SELECT s.id, s.full_name, s.contact_number, s.town,
            (SELECT COUNT(*) FROM attendance a 
             WHERE a.student_id = s.id 
             AND a.class_id = '$filter_class' 
             AND MONTHNAME(a.date) = '$filter_month' 
             AND YEAR(a.date) = '$filter_year') as days_attended
            FROM student s
            JOIN student_classes sc ON s.id = sc.student_id
            WHERE sc.class_id = '$filter_class'
            -- Logic: Must have at least 1 attendance record for the selected month
            AND EXISTS (
                SELECT 1 FROM attendance a 
                WHERE a.student_id = s.id 
                AND a.class_id = '$filter_class' 
                AND MONTHNAME(a.date) = '$filter_month'
                AND YEAR(a.date) = '$filter_year'
            )
            -- Logic: Must NOT have a payment record for the selected month
            AND s.id NOT IN (
                SELECT p.student_id 
                FROM class_fees_payments p 
                WHERE p.class_id = '$filter_class' 
                AND p.payment_month = '$filter_month'
            )
            ORDER BY days_attended DESC, s.full_name ASC";
    
    $unpaid_result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Defaulters - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .glass-card { background: rgba(255, 255, 255, 0.98); border-radius: 2rem; border: 1px solid #fee2e2; }
        .defaulter-row { background: white; border: 1px solid #f1f5f9 !important; transition: 0.3s; }
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 65px; background: #0f172a; display: flex; justify-content: space-around; align-items: center; z-index: 1000; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen pb-32">

    <div class="bg-slate-900 text-white pb-32 pt-10 px-6 rounded-b-[4rem] shadow-2xl">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="class_fees.php" class="bg-white/10 p-3 rounded-2xl border border-white/20"><i class="fas fa-arrow-left"></i></a>
                <span class="text-[10px] font-black uppercase tracking-widest bg-rose-800 px-3 py-1 rounded-lg">Attendance Based Tracker</span>
            </div>
            <h1 class="text-3xl font-black italic uppercase tracking-tighter">Unpaid List</h1>
            <p class="text-rose-100 text-[10px] font-bold uppercase mt-2 opacity-80 italic">Showing students who attended class but didn't pay.</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-20">
        <div class="glass-card shadow-2xl p-6 md:p-8">
            
            <form action="" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <select name="class_id" class="p-4 rounded-2xl border-2 border-slate-100 font-bold outline-none focus:border-rose-400" onchange="this.form.submit()" required>
                    <option value="">Select Class...</option>
                    <?php while($c = $classes_list->fetch_assoc()): ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($filter_class == $c['class_id']) ? 'selected' : '' ?>>
                            <?= $c['exam_year'] ?> <?= $c['subject'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <select name="month" class="p-4 rounded-2xl border-2 border-slate-100 font-bold outline-none focus:border-rose-400" onchange="this.form.submit()">
                    <?php 
                    $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                    foreach($months as $m) echo "<option value='$m' ".($filter_month==$m?'selected':'').">$m</option>";
                    ?>
                </select>
            </form>

            <?php if (!empty($filter_class)): ?>
            <div class="space-y-4">
                <?php if($unpaid_result && $unpaid_result->num_rows > 0): ?>
                    <?php while($row = $unpaid_result->fetch_assoc()): ?>
                        <div class="defaulter-row p-5 rounded-3xl flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center font-black text-[10px]">
                                    #<?= $row['id'] ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-800 uppercase italic"><?= $row['full_name'] ?></h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase"><?= $row['town'] ?></span>
                                        <span class="text-[9px] font-black text-rose-500 bg-rose-50 px-2 py-0.5 rounded uppercase">
                                            Attended <?= $row['days_attended'] ?> Days
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="tel:<?= $row['contact_number'] ?>" class="w-10 h-10 bg-slate-900 text-white rounded-xl flex items-center justify-center"><i class="fas fa-phone-alt text-xs"></i></a>
                                <a href="https://wa.me/94<?= ltrim($row['contact_number'], '0') ?>" class="w-10 h-10 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-100"><i class="fab fa-whatsapp text-lg"></i></a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-20 opacity-30">
                        <i class="fas fa-user-check text-4xl mb-4"></i>
                        <p class="text-[10px] font-black uppercase tracking-widest">No defaulters found for this month.</p>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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