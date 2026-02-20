<?php
session_start();
include 'config.php';

// ටීචර් කෙනෙක්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'teacher') {
    header("Location: login.php");
    exit();
}

// 1. Approve කිරීමේ ක්‍රියාවලිය
if (isset($_GET['approve_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['approve_id']);
    $conn->query("UPDATE student SET status = 'active' WHERE id = '$id'");
    header("Location: approve.php?msg=approved");
    exit();
}

// 2. Reject/Delete කිරීමේ ක්‍රියාවලිය
if (isset($_GET['reject_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['reject_id']);
    // ශිෂ්‍යයා සම්බන්ධ පන්ති දත්ත මුලින් ඉවත් කරයි
    $conn->query("DELETE FROM student_classes WHERE student_id = '$id'");
    $conn->query("DELETE FROM student WHERE id = '$id'");
    header("Location: approve.php?msg=rejected");
    exit();
}

// 3. Pending ශිෂ්‍යයන් සහ ඔවුන්ගේ පන්ති ලබා ගැනීම
$sql = "SELECT s.*, GROUP_CONCAT(c.subject SEPARATOR ', ') as enrolled_classes 
        FROM student s
        LEFT JOIN student_classes sc ON s.id = sc.student_id
        LEFT JOIN classes c ON sc.class_id = c.class_id
        WHERE s.status = 'pending'
        GROUP BY s.id
        ORDER BY s.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Students - LMS Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .admin-header { background: #0f172a; border-bottom: 4px solid #4f46e5; }
        .pending-card { transition: all 0.3s ease; border-left: 4px solid #f59e0b; }
        .pending-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="bg-slate-50 pb-10">

    <header class="admin-header pt-8 pb-10 px-6 text-white">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Student Approvals</h1>
                <p class="text-indigo-400 text-[10px] font-bold uppercase tracking-widest">Pending Verification Requests</p>
            </div>
            <a href="admin_page.php" class="bg-white/10 px-4 py-2 rounded-xl text-[10px] font-black uppercase border border-white/10 hover:bg-white/20 transition-all">Back to Dashboard</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 -mt-6">
        
        <div class="grid grid-cols-1 gap-4">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white p-5 rounded-3xl shadow-sm pending-card flex flex-col md:flex-row md:items-center justify-between gap-6">
                        
                        <div class="flex items-center gap-4">
                            <img src="uploads/profile_pics/<?= $row['profile_pic'] ?>" class="w-14 h-14 rounded-2xl object-cover border-2 border-slate-100 shadow-sm">
                            <div>
                                <h3 class="text-sm font-black text-slate-800 uppercase italic leading-tight"><?= $row['full_name'] ?></h3>
                                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-tight">
                                    <i class="fas fa-school mr-1 text-indigo-400"></i> <?= $row['school'] ?>
                                </p>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="text-[9px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-md font-bold uppercase">
                                        NIC: <?= $row['nic_number'] ?>
                                    </span>
                                    <span class="text-[9px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-bold uppercase">
                                        Mob: <?= $row['contact_number'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 md:border-l md:pl-6 border-slate-100">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Enrollment Classes</span>
                            <p class="text-[11px] font-extrabold text-slate-600 uppercase mt-1">
                                <?= $row['enrolled_classes'] ? $row['enrolled_classes'] : 'No classes selected' ?>
                            </p>
                        </div>

                        <div class="flex gap-2">
                            <a href="approve.php?approve_id=<?= $row['id'] ?>" class="flex-1 md:flex-none px-6 py-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg shadow-indigo-100 flex items-center justify-center gap-2">
                                <i class="fas fa-check"></i> Approve
                            </a>
                            <button onclick="confirmReject(<?= $row['id'] ?>)" class="flex-1 md:flex-none px-4 py-3 bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white py-20 rounded-3xl text-center border-2 border-dashed border-slate-200">
                    <i class="fas fa-user-clock text-4xl text-slate-200 mb-4"></i>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">No pending registrations found</p>
                </div>
            <?php endif; ?>
        </div>
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

    <script>
        // Reject කිරීම තහවුරු කිරීමට Alert එකක් පෙන්වීම
        function confirmReject(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the registration request!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Reject It!',
                customClass: { popup: 'rounded-[2rem]' }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'approve.php?reject_id=' + id;
                }
            })
        }

        // සාර්ථක වුණාම Alert එකක් පෙන්වීම
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('msg') === 'approved') {
            Swal.fire({ icon: 'success', title: 'Student Approved!', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-[2rem]' }});
        }
        if (urlParams.get('msg') === 'rejected') {
            Swal.fire({ icon: 'info', title: 'Request Rejected!', timer: 2000, showConfirmButton: false, customClass: { popup: 'rounded-[2rem]' }});
        }
    </script>

</body>
</html>