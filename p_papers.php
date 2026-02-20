<?php
session_start();
include 'config.php';

// Teacher Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Database එකෙන් Papers ටික ගන්නවා (Year එක අනුව අලුත් ඒව උඩට එන්න)
$sql = "SELECT * FROM past_papers ORDER BY year DESC, created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Papers - LMS Pro</title>
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
        
        /* Mobile scroll protection and table styles */
        .table-container { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }

        /* පේළි වල hover effect එක */
        .table-row { transition: all 0.3s ease; }
        .table-row:hover { transform: scale(1.01); }
    </style>
</head>
<body class="min-h-screen pb-32">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="admin_page.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <a href="add_p_papers.php" class="w-11 h-11 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20 active:scale-90 hover:bg-indigo-500 transition-all">
                    <i class="fas fa-plus text-white"></i>
                </a>
            </div>
            
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Past Papers</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Manage Student Resources</p>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-16 relative z-10">
        <div class="glass-card shadow-2xl p-5 md:p-8">
            
            <div class="hidden md:block table-container">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] uppercase font-black text-slate-400 tracking-widest">
                            <th class="px-6 py-2">Info</th>
                            <th class="px-6 py-2">Title & Category</th>
                            <th class="px-6 py-2 text-center">Format</th>
                            <th class="px-6 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="bg-white table-row rounded-2xl group shadow-sm border border-slate-100">
                                    <td class="px-6 py-4 first:rounded-l-2xl">
                                        <div class="w-12 h-12 bg-slate-100 rounded-xl flex flex-col items-center justify-center text-slate-600 font-black group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                            <span class="text-[8px] uppercase opacity-60">Year</span>
                                            <span class="text-xs"><?= $row['year'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <h3 class="text-xs font-black text-slate-800 uppercase italic"><?= htmlspecialchars($row['title']) ?></h3>
                                        <p class="text-[9px] font-bold <?= $row['category'] == 'Paper' ? 'text-indigo-500' : 'text-emerald-500' ?> uppercase tracking-widest mt-0.5">
                                            <?= $row['category'] ?>
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-[9px] font-black px-3 py-1 rounded-full border <?= $row['file_source'] == 'upload' ? 'bg-rose-50 text-rose-500 border-rose-100' : 'bg-blue-50 text-blue-500 border-blue-100' ?> uppercase">
                                            <?= $row['file_source'] == 'upload' ? 'PDF File' : 'External Link' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 last:rounded-r-2xl text-right">
                                        <div class="flex justify-end gap-2">
                                            <?php $target_link = ($row['file_source'] == 'upload') ? 'uploads/past_papers/'.$row['paper_file'] : $row['paper_file']; ?>
                                            <a href="<?= $target_link ?>" target="_blank" class="w-9 h-9 bg-slate-900 text-white rounded-lg inline-flex items-center justify-center hover:bg-indigo-600 transition-all">
                                                <i class="fas fa-external-link-alt text-[10px]"></i>
                                            </a>
                                            <button onclick="deletePaper(<?= $row['id'] ?>)" class="w-9 h-9 bg-rose-50 text-rose-500 rounded-lg inline-flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                                <i class="fas fa-trash-alt text-[10px]"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-20 opacity-30 font-black text-[10px] uppercase">No Papers Added Yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="md:hidden space-y-4">
                <?php 
                if($result->num_rows > 0): 
                    $result->data_seek(0); 
                    while($row = $result->fetch_assoc()): 
                ?>
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-900 rounded-2xl flex flex-col items-center justify-center text-white shrink-0 shadow-lg shadow-slate-200">
                            <span class="text-[8px] font-black uppercase opacity-50">Year</span>
                            <span class="text-xs font-black"><?= $row['year'] ?></span>
                        </div>
                        <div>
                            <h3 class="text-[11px] font-black text-slate-800 uppercase italic leading-tight mb-1"><?= htmlspecialchars($row['title']) ?></h3>
                            <span class="text-[9px] font-black <?= $row['category'] == 'Paper' ? 'text-indigo-500' : 'text-emerald-500' ?> uppercase tracking-tighter italic"><?= $row['category'] ?></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <?php $m_link = ($row['file_source'] == 'upload') ? 'uploads/past_papers/'.$row['paper_file'] : $row['paper_file']; ?>
                        <a href="<?= $m_link ?>" target="_blank" class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center active:scale-90">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="deletePaper(<?= $row['id'] ?>)" class="w-10 h-10 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center active:scale-90">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>

        </div>
    </main>

    <nav class="bottom-nav lg:hidden border-t border-white/10 shadow-[0_-10px_30px_rgba(0,0,0,0.2)]">
        <a href="index.php" class="flex flex-col items-center text-slate-500">
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Home</span>
        </a>
        <a href="admin_page.php" class="flex flex-col items-center text-indigo-400">
            <i class="fas fa-user text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Profile</span>
        </a>
        <a href="logout.php" class="text-rose-500 flex flex-col items-center">
            <i class="fas fa-power-off text-lg"></i>
            <span class="text-[9px] font-bold uppercase mt-1">Exit</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deletePaper(id) {
            Swal.fire({
                title: 'Delete Paper?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Confirm',
                borderRadius: '25px'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'process/delete_paper.php?id=' + id;
                }
            })
        }
    </script>

    <?php
    if (isset($_GET['status']) && $_GET['status'] == 'deleted') {
        echo "<script>Swal.fire({ title: 'Deleted!', icon: 'success', showConfirmButton: false, timer: 1500, borderRadius: '25px' });</script>";
    }
    ?>
</body>
</html>