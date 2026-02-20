<?php
session_start();
include 'config.php';

/**
 * 1. TEACHER LOGIN CHECK
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$filter_class = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : '';

/**
 * 2. STATUS UPDATE LOGIC (Fixed for ENUM values)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $live_id = mysqli_real_escape_string($conn, $_POST['live_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']); // Will be 'Upcoming', 'Live', or 'Ended'
    
    $update_sql = "UPDATE live_classes SET status = '$new_status' WHERE id = '$live_id' AND teacher_id = '$teacher_id'";
    $conn->query($update_sql);
    
    // Refresh to update UI and prevent form resubmission
    $redirect_url = $_SERVER['PHP_SELF'] . (!empty($filter_class) ? "?class_id=$filter_class" : "");
    header("Location: " . $redirect_url);
    exit();
}

/**
 * 3. GET DATA
 */
$sql = "SELECT l.*, c.subject, c.exam_year, c.stream 
        FROM live_classes l 
        JOIN classes c ON l.class_id = c.class_id 
        WHERE l.teacher_id = '$teacher_id'";

if (!empty($filter_class)) { $sql .= " AND l.class_id = '$filter_class'"; }
$sql .= " ORDER BY l.live_date DESC, l.live_time DESC";

$result = $conn->query($sql);
$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Hub - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2.5rem; border: 1px solid white; }
        .countdown-text { font-family: 'Courier New', Courier, monospace; }
        .live-pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }
        .bottom-nav { position: fixed; bottom: 0; left: 0; width: 100%; height: 60px; background: #0f172a; display: flex; justify-content: space-around; align-items: center; z-index: 1000; }
    </style>
</head>
<body class="pb-28">

    <div class="bg-slate-900 text-white pb-32 pt-10 px-6 rounded-b-[4rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <a href="admin_page.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                <i class="fas fa-arrow-left text-indigo-400"></i> Back
            </a>
            <div>
                <h1 class="text-3xl font-black italic tracking-tighter uppercase text-rose-500">Live Control</h1>
                <p class="text-slate-400 text-[9px] font-bold uppercase tracking-widest">Manage your streams & status</p>
            </div>
            <a href="create_live.php" class="h-12 w-12 bg-rose-600 rounded-2xl flex items-center justify-center shadow-lg hover:scale-110 transition-all">
                <i class="fas fa-plus text-white"></i>
            </a>
        </div>
    </div>

    <main class="max-w-5xl mx-auto px-4 -mt-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): 
                    $target_dt = $row['live_date'] . ' ' . $row['live_time'];
                    $db_status = $row['status']; // Current ENUM value from DB
                ?>
                    <div class="glass-card p-6 shadow-xl border border-slate-100">
                        
                        <div class="flex justify-between items-center mb-5">
                            <div class="countdown-display px-3 py-1.5 bg-slate-900 text-amber-400 rounded-xl text-[10px] font-black countdown-text" data-time="<?= $target_dt ?>">
                                SYNCING...
                            </div>
                            <div class="flex gap-3">
                                <button onclick="shareLink('<?= addslashes($row['topic']) ?>', '<?= $row['meeting_link'] ?>')" class="text-slate-400 hover:text-indigo-600 transition-all">
                                    <i class="fas fa-share-nodes text-lg"></i>
                                </button>
                                <a href="delete_live.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this class?')" class="text-slate-300 hover:text-rose-500">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </a>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 rounded-2xl flex flex-col items-center justify-center 
                                <?php 
                                    if($row['media'] == 'Zoom') echo 'bg-blue-50 text-blue-600';
                                    elseif($row['media'] == 'YouTube') echo 'bg-red-50 text-red-600';
                                    else echo 'bg-slate-100 text-slate-500';
                                ?>">
                                <i class="fa-2x <?php 
                                    if($row['media'] == 'Zoom') echo 'fas fa-video';
                                    elseif($row['media'] == 'YouTube') echo 'fab fa-youtube';
                                    else echo 'fas fa-link';
                                ?>"></i>
                                <span class="text-[7px] font-black uppercase mt-1"><?= $row['media'] ?></span>
                            </div>

                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] font-black text-rose-500 uppercase tracking-widest italic"><?= $row['exam_year'] ?> <?= $row['stream'] ?></span>
                                    
                                    <?php if($db_status == 'Live'): ?>
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[8px] font-black rounded uppercase live-pulse">Live</span>
                                    <?php elseif($db_status == 'Ended'): ?>
                                        <span class="px-2 py-0.5 bg-slate-200 text-slate-500 text-[8px] font-black rounded uppercase">Ended</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-600 text-[8px] font-black rounded uppercase">Upcoming</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="text-lg font-black text-slate-800 uppercase italic leading-none my-1"><?= $row['topic'] ?></h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase"><?= $row['subject'] ?></p>

                                <form method="POST" action="" class="mt-3">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="live_id" value="<?= $row['id'] ?>">
                                    <div class="flex items-center gap-2">
                                        <label class="text-[8px] font-black text-slate-400 uppercase">Change Status:</label>
                                        <select name="status" onchange="this.form.submit()" class="text-[9px] font-black uppercase bg-white border border-slate-200 text-slate-700 rounded-lg px-2 py-1 outline-none focus:ring-2 focus:ring-rose-500 transition-all cursor-pointer">
                                            <option value="Upcoming" <?= ($db_status == 'Upcoming') ? 'selected' : '' ?>>Upcoming</option>
                                            <option value="Live" <?= ($db_status == 'Live') ? 'selected' : '' ?>>Live Now</option>
                                            <option value="Ended" <?= ($db_status == 'Ended') ? 'selected' : '' ?>>Ended</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <i class="far fa-calendar-alt text-rose-500"></i>
                                <div class="flex flex-col">
                                    <span class="text-[8px] text-slate-400 font-bold uppercase">Date</span>
                                    <span class="text-[10px] font-black text-slate-700 uppercase"><?= date('M d, Y', strtotime($row['live_date'])) ?></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <i class="far fa-clock text-rose-500"></i>
                                <div class="flex flex-col">
                                    <span class="text-[8px] text-slate-400 font-bold uppercase">Time</span>
                                    <span class="text-[10px] font-black text-slate-700 uppercase"><?= date('h:i A', strtotime($row['live_time'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <a href="<?= $row['admin_link'] ?>" target="_blank" class="bg-rose-600 text-white py-4 rounded-2xl font-black text-[9px] uppercase tracking-widest text-center shadow-lg shadow-rose-500/20 hover:bg-rose-700 transition-all">
                                <i class="fas fa-lock-open mr-2"></i> Admin Panel
                            </a>
                            <a href="<?= $row['meeting_link'] ?>" target="_blank" class="bg-slate-900 text-white py-4 rounded-2xl font-black text-[9px] uppercase tracking-widest text-center hover:bg-black transition-all">
                                <i class="fas fa-users mr-2"></i> Student Link
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center opacity-30">
                    <i class="fas fa-video-slash text-5xl mb-4 text-slate-400"></i>
                    <p class="font-black uppercase tracking-widest text-xs text-slate-500">No Scheduled Classes</p>
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

    <script>
    // Share Function
    function shareLink(topic, link) {
        const text = `🎓 *LIVE CLASS NOTIFICATION* \n\n*Topic:* ${topic}\n*Join here:* ${link}\n\nDon't miss it! 🚀`;
        if (navigator.share) {
            navigator.share({ title: 'Live Class', text: text, url: link });
        } else {
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }
    }

    // Countdown Timer Logic
    function updateTimers() {
        document.querySelectorAll('.countdown-display').forEach(display => {
            const target = new Date(display.getAttribute('data-time')).getTime();
            const now = new Date().getTime();
            const diff = target - now;

            if (diff < 0) {
                display.innerHTML = "🔴 SESSION IS SCHEDULED";
                display.classList.add('bg-rose-100', 'text-rose-600');
                display.classList.remove('bg-slate-900', 'text-amber-400');
                return;
            }

            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const s = Math.floor((diff % (1000 * 60)) / 1000);

            display.innerHTML = `⏳ ${d}D : ${h}H : ${m}M : ${s}S`;
        });
    }

    setInterval(updateTimers, 1000);
    updateTimers();
    </script>
</body>
</html>