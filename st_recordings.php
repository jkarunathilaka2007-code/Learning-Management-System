<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$selected_folder = isset($_GET['folder']) ? mysqli_real_escape_string($conn, $_GET['folder']) : '';
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// ශිෂ්‍යයාගේ පන්ති පමණක් ලබා ගැනීම
$classes_query = "SELECT c.class_id, c.subject FROM classes c 
                  JOIN student_classes sc ON c.class_id = sc.class_id 
                  WHERE sc.student_id = '$student_id'";
$classes_result = $conn->query($classes_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>LMS Pro - Study Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-header { background: #0f172a; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .folder-card { background: #1e293b; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); transition: all 0.2s ease; }
        .folder-card h3 { color: #f8fafc; }
        .folder-card:active { transform: scale(0.96); background: #0f172a; }
        .search-container { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.1); }
        ::-webkit-scrollbar { display: none; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-24">

    <header class="glass-header sticky top-0 z-50 pt-6 pb-6 px-5 rounded-b-[2.5rem]">
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center mb-5">
                <div class="flex items-center gap-3">
                    <a href="<?= $selected_folder ? 'st_recordings.php' : 'student_page.php' ?>" 
                       class="w-9 h-9 flex items-center justify-center bg-white/10 rounded-full hover:bg-white/20 transition-all">
                        <i class="fas fa-chevron-left text-white text-xs"></i>
                    </a>
                    <div>
                        <p class="text-[9px] font-bold text-indigo-400 uppercase tracking-[0.2em] mb-1">Learning Vault</p>
                        <h1 class="text-lg font-extrabold text-white leading-tight">
                            <?= $selected_folder ? $selected_folder : 'My Collections' ?>
                        </h1>
                    </div>
                </div>
                <a href="st_recording_history.php" class="w-10 h-10 flex items-center justify-center bg-indigo-500 rounded-2xl shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-history text-white text-sm"></i>
                </a>
            </div>

            <form action="" method="GET" class="relative">
                <div class="search-container flex items-center px-4 py-3 rounded-2xl">
                    <i class="fas fa-search text-slate-400 text-sm mr-3"></i>
                    <input type="text" name="search" value="<?= $search_query ?>" placeholder="Search lessons..." 
                           class="bg-transparent border-none w-full text-white text-sm font-medium outline-none placeholder:text-slate-500">
                    <?php if($selected_folder): ?> <input type="hidden" name="folder" value="<?= $selected_folder ?>"> <?php endif; ?>
                </div>
            </form>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-5 mt-8">
        
        <div class="flex gap-2 overflow-x-auto pb-4 mb-4 no-scrollbar">
            <a href="st_recordings.php" class="whitespace-nowrap px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider <?= empty($filter_class) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-500 border border-slate-200' ?>">
                All Classes
            </a>
            <?php 
            mysqli_data_seek($classes_result, 0);
            while($c = $classes_result->fetch_assoc()): ?>
                <a href="?class_id=<?= $c['class_id'] ?><?= $selected_folder ? '&folder='.urlencode($selected_folder) : '' ?>" 
                   class="whitespace-nowrap px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider <?= ($filter_class == $c['class_id']) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white text-slate-500 border border-slate-200' ?>">
                    <?= $c['subject'] ?>
                </a>
            <?php endwhile; ?>
        </div>

        <?php if (empty($selected_folder)): ?>
            <div class="grid grid-cols-2 gap-4">
                <?php
                // Folder Query with status='released'
                $f_sql = "SELECT r.folder_name, COUNT(*) as v_count FROM recordings r 
                          JOIN student_classes sc ON r.class_id = sc.class_id
                          WHERE sc.student_id = '$student_id' AND r.status = 'released'";
                
                if (!empty($filter_class)) $f_sql .= " AND r.class_id = '$filter_class'";
                if (!empty($search_query)) $f_sql .= " AND (r.video_title LIKE '%$search_query%' OR r.folder_name LIKE '%$search_query%')";
                
                $f_sql .= " GROUP BY r.folder_name ORDER BY r.added_date DESC";
                $f_res = $conn->query($f_sql);

                if($f_res->num_rows > 0):
                    while($f = $f_res->fetch_assoc()): ?>
                        <a href="?folder=<?= urlencode($f['folder_name']) ?>" class="folder-card p-5 rounded-[2rem] flex flex-col items-center text-center">
                            <div class="w-12 h-12 bg-white/5 text-indigo-400 rounded-2xl flex items-center justify-center mb-3">
                                <i class="fas fa-folder-closed text-xl"></i>
                            </div>
                            <h3 class="text-[11px] font-extrabold uppercase leading-tight line-clamp-2 min-h-[2rem]">
                                <?= $f['folder_name'] ?>
                            </h3>
                            <div class="mt-2 bg-indigo-500/20 text-indigo-300 text-[8px] font-black px-3 py-1 rounded-lg uppercase">
                                <?= $f['v_count'] ?> Videos
                            </div>
                        </a>
                    <?php endwhile;
                else: ?>
                    <div class="col-span-2 py-10 text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest">No Folders Found</div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php
                // Video Query with status='released'
                $v_sql = "SELECT r.* FROM recordings r JOIN student_classes sc ON r.class_id = sc.class_id 
                          WHERE r.folder_name = '$selected_folder' 
                          AND sc.student_id = '$student_id' 
                          AND r.status = 'released'";
                if (!empty($search_query)) $v_sql .= " AND r.video_title LIKE '%$search_query%'";
                $v_sql .= " ORDER BY r.added_date DESC";
                $v_res = $conn->query($v_sql);

                if($v_res->num_rows > 0):
                    while($v = $v_res->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center gap-4">
                            <div class="w-11 h-11 bg-slate-900 text-white rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-play text-[10px]"></i>
                            </div>
                            <div class="flex-1 overflow-hidden">
                                <h4 class="text-[11px] font-black text-slate-800 truncate uppercase mb-1"><?= $v['video_title'] ?></h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter"><?= $v['duration'] ?> • <?= $v['sub_title'] ?></p>
                            </div>
                            <a href="watch_video.php?id=<?= $v['id'] ?>" class="bg-indigo-600 text-white w-9 h-9 flex items-center justify-center rounded-xl shadow-md">
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    <?php endwhile; 
                else: ?>
                     <div class="py-10 text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest">No Released Videos Found</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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