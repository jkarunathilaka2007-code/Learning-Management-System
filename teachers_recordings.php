<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$selected_folder = isset($_GET['folder']) ? mysqli_real_escape_string($conn, $_GET['folder']) : null;
$selected_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : null;

// Delete Logic
if (isset($_GET['delete_id'])) {
    $del_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $conn->query("DELETE FROM recordings WHERE id = '$del_id'");
    header("Location: teachers_recordings.php?folder=$selected_folder&class_id=$selected_class");
}

$classes = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recording Studio - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; }
        
        .folder-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1.5rem; }
        .folder-box { background: white; border-radius: 2rem; padding: 1.5rem; text-align: center; border: 1px solid #e2e8f0; transition: all 0.3s ease; }
        .folder-box:hover { transform: translateY(-5px); border-color: #6366f1; box-shadow: 0 15px 30px -10px rgba(99, 102, 241, 0.2); }
        
        .video-card { background: white; border-radius: 1.5rem; padding: 1.25rem; border: 1px solid #e2e8f0; transition: 0.3s; }
        .video-card:hover { border-color: #6366f1; background: #fcfdff; }
        
        .status-dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-24">

    <header class="bg-slate-900 text-white pt-12 pb-24 px-6 rounded-b-[3.5rem] shadow-2xl relative">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <h1 class="text-2xl font-black uppercase italic tracking-tighter">Content <span class="text-indigo-400">Library</span></h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Manage & Stream Recordings</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="add_recording.php" class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center hover:bg-white hover:text-indigo-600 transition-all shadow-xl">
                    <i class="fas fa-plus"></i>
                </a>
                <form method="GET" id="filterForm">
                    <select name="class_id" onchange="this.form.submit()" class="bg-white/10 border border-white/10 p-3 rounded-2xl text-xs font-bold outline-none cursor-pointer hover:bg-white/20">
                        <option value="" class="text-slate-900">All Academic Classes</option>
                        <?php 
                        $classes->data_seek(0);
                        while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= $selected_class == $c['class_id'] ? 'selected' : '' ?> class="text-slate-900">
                                <?= $c['exam_year'] ?> - <?= $c['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
                <a href="admin_page.php" class="w-12 h-12 bg-red-600 rounded-2xl flex items-center justify-center hover:bg-white hover:text-indigo-600 transition-all shadow-xl">
                    <i class="fas fa-close"></i>
                </a>
                
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 -mt-10 relative z-10">

        <?php if (!$selected_folder): ?>
            <div class="folder-grid">
                <?php
                $where = $selected_class ? "AND class_id = '$selected_class'" : "";
                $folders = $conn->query("SELECT folder_name, COUNT(*) as qty FROM recordings WHERE class_id IN (SELECT class_id FROM classes WHERE teacher_id = '$teacher_id') $where GROUP BY folder_name");

                while($f = $folders->fetch_assoc()): ?>
                    <a href="?folder=<?= urlencode($f['folder_name']) ?>&class_id=<?= $selected_class ?>" class="folder-box group">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:bg-indigo-50 transition-colors">
                            <i class="fas fa-folder text-3xl text-slate-300 group-hover:text-indigo-500"></i>
                        </div>
                        <h3 class="font-black text-slate-800 text-[10px] uppercase tracking-tight truncate"><?= $f['folder_name'] ?></h3>
                        <p class="text-[8px] font-bold text-slate-400 mt-1"><?= $f['qty'] ?> RECORDINGS</p>
                    </a>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="space-y-4">
                <div class="flex items-center justify-between mb-6 px-2">
                    <div class="flex items-center gap-4">
                        <a href="teachers_recordings.php?class_id=<?= $selected_class ?>" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm text-slate-400 hover:text-indigo-600">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2 class="text-lg font-black italic uppercase text-slate-900"><?= $selected_folder ?></h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php
                    $recs = $conn->query("SELECT r.*, c.subject FROM recordings r JOIN classes c ON r.class_id = c.class_id WHERE r.folder_name = '$selected_folder' AND c.teacher_id = '$teacher_id' ORDER BY r.added_date DESC");
                    
                    while($r = $recs->fetch_assoc()): 
                        $is_released = ($r['status'] == 'released');
                        $is_scheduled = ($r['status'] == 'scheduled');
                        $dot_color = $is_released ? 'bg-emerald-500' : ($is_scheduled ? 'bg-amber-500' : 'bg-rose-500');
                    ?>
                        <div class="video-card">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-indigo-400 shadow-lg shrink-0">
                                        <i class="fas fa-play text-xs"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="flex items-center gap-2">
                                            <span class="status-dot <?= $dot_color ?>"></span>
                                            <h4 class="font-black text-slate-900 text-xs truncate leading-none"><?= $r['video_title'] ?></h4>
                                        </div>
                                        <p class="text-[10px] text-slate-400 font-bold mt-1 truncate"><?= $r['sub_title'] ?></p>
                                        <div class="flex items-center gap-3 mt-2">
                                            <span class="text-[8px] font-black px-2 py-0.5 bg-slate-100 rounded text-slate-500 tracking-tighter"><?= $r['duration'] ?></span>
                                            <span class="text-[8px] font-black px-2 py-0.5 bg-indigo-50 rounded text-indigo-500 tracking-tighter"><?= $r['subject'] ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <a href="?delete_id=<?= $r['id'] ?>&folder=<?= urlencode($selected_folder) ?>&class_id=<?= $selected_class ?>" 
                                       onclick="return confirm('Delete this lesson?')" 
                                       class="text-slate-300 hover:text-rose-500 transition-colors p-1">
                                        <i class="far fa-trash-alt text-[10px]"></i>
                                    </a>
                                    <a href="edit_recording.php?id=<?= $r['id'] ?>" class="text-slate-300 hover:text-blue-500 transition-colors p-1">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="mt-5 pt-4 border-t border-slate-50">
                                <a href="view_recordings.php?class_id=<?= $r['class_id'] ?>" class="w-full py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase italic tracking-widest flex items-center justify-center gap-2 hover:bg-indigo-600 transition-all shadow-lg">
                                    <i class="fas fa-play-circle"></i> Watch Now
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

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