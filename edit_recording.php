<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$msg = "";

// 1. කලින් දත්ත ලබා ගැනීම (Fetch Data)
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = $conn->query("SELECT * FROM recordings WHERE id = '$id'");
    $recording = $res->fetch_assoc();

    // Duration එක පැය සහ මිනිත්තු වලට වෙන් කිරීම (Ex: 01h 45m -> 01, 45)
    preg_match('/(\d+)h\s+(\d+)m/', $recording['duration'], $time_matches);
    $saved_hrs = $time_matches[1] ?? "00";
    $saved_mins = $time_matches[2] ?? "00";
}

// 2. දත්ත යාවත්කාලීන කිරීම (Update Logic)
if (isset($_POST['update_video'])) {
    $id = $_POST['id'];
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $video_title = mysqli_real_escape_string($conn, $_POST['video_title']);
    $sub_title = mysqli_real_escape_string($conn, $_POST['sub_title']);
    $raw_url = $_POST['video_url'];
    $duration = $_POST['duration_hrs'] . "h " . $_POST['duration_mins'] . "m";
    $folder_name = !empty($_POST['new_folder']) ? mysqli_real_escape_string($conn, $_POST['new_folder']) : mysqli_real_escape_string($conn, $_POST['folder_name']);
    $status = $_POST['status'];
    $expire_date = !empty($_POST['expire_date']) ? "'".mysqli_real_escape_string($conn, $_POST['expire_date'])."'" : "NULL";

    // YouTube ID එක නැවත Extract කිරීම
    function getYouTubeId($url) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return isset($match[1]) ? $match[1] : null;
    }
    $video_id = getYouTubeId($raw_url) ?: $recording['video_id'];

    $sql = "UPDATE recordings SET 
            class_id='$class_id', video_title='$video_title', sub_title='$sub_title', 
            video_id='$video_id', folder_name='$folder_name', duration='$duration', 
            status='$status', expire_date=$expire_date 
            WHERE id='$id'";

    if ($conn->query($sql)) {
        header("Location: teachers_recordings.php?folder=" . urlencode($folder_name));
        exit();
    } else {
        $msg = "error";
    }
}

$classes = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id'");
$existing_folders = $conn->query("SELECT DISTINCT folder_name FROM recordings");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Recording - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .input-box { width: 100%; padding: 1rem; background: #fff; border: 2px solid #e2e8f0; border-radius: 1.2rem; font-weight: 600; outline: none; transition: 0.3s; }
        .input-box:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .section-label { font-size: 10px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; display: block; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-20">

    <header class="bg-slate-900 text-white pt-16 pb-32 px-6 rounded-b-[3.5rem] shadow-2xl relative overflow-hidden">
        <div class="max-w-xl mx-auto flex justify-between items-center relative z-10">
            <a href="teachers_recordings.php" class="w-11 h-11 flex items-center justify-center bg-white/10 rounded-2xl hover:bg-white/20 transition-all"><i class="fas fa-times"></i></a>
            <h1 class="text-xl font-black italic uppercase tracking-tighter text-center">Update<br><span class="text-indigo-200 text-xs tracking-[0.3em]">Recording Details</span></h1>
            <div class="w-11"></div>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 -mt-20 relative z-20">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200 border border-white">
            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?= $recording['id'] ?>">
                
                <div>
                    <label class="section-label">01. Target Class</label>
                    <select name="class_id" required class="input-box">
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= $recording['class_id'] == $c['class_id'] ? 'selected' : '' ?>>
                                <?= $c['exam_year'] ?> - <?= $c['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="section-label">02. Folder</label>
                        <select name="folder_name" class="input-box">
                            <?php while($f = $existing_folders->fetch_assoc()): ?>
                                <option value="<?= $f['folder_name'] ?>" <?= $recording['folder_name'] == $f['folder_name'] ? 'selected' : '' ?>>
                                    <?= $f['folder_name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="section-label">Rename Folder</label>
                        <input type="text" name="new_folder" placeholder="New name..." class="input-box border-dashed border-indigo-200">
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="section-label">03. Title</label>
                        <input type="text" name="video_title" value="<?= $recording['video_title'] ?>" required class="input-box">
                    </div>
                    <div>
                        <label class="section-label">Subtitle</label>
                        <input type="text" name="sub_title" value="<?= $recording['sub_title'] ?>" class="input-box">
                    </div>
                </div>

                <div>
                    <label class="section-label">04. Length</label>
                    <div class="flex gap-3">
                        <select name="duration_hrs" class="input-box">
                            <?php for($i=0; $i<=10; $i++): $v = sprintf("%02d", $i); ?>
                                <option value="<?= $v ?>" <?= $saved_hrs == $v ? 'selected' : '' ?>><?= $i ?> Hours</option>
                            <?php endfor; ?>
                        </select>
                        <select name="duration_mins" class="input-box">
                            <?php for($i=0; $i<=59; $i++): $v = sprintf("%02d", $i); ?>
                                <option value="<?= $v ?>" <?= $saved_mins == $v ? 'selected' : '' ?>><?= $i ?> Minutes</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="section-label">05. Source URL</label>
                    <input type="text" name="video_url" value="https://www.youtube.com/watch?v=<?= $recording['video_id'] ?>" required class="input-box">
                </div>

                <div>
                    <label class="section-label">06. Visibility Control</label>
                    <button type="button" onclick="openModal()" class="w-full p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-between group hover:border-indigo-500 transition-all">
                        <span id="status-label" class="text-xs font-black text-slate-500 uppercase">
                            <i class="far fa-calendar-alt mr-2"></i> Status: <?= ucfirst($recording['status']) ?>
                        </span>
                        <i class="fas fa-edit text-slate-300 group-hover:text-indigo-500"></i>
                    </button>
                    <input type="hidden" name="status" id="form_status" value="<?= $recording['status'] ?>">
                    <input type="hidden" name="expire_date" id="form_expire_date" value="<?= $recording['expire_date'] ?>">
                </div>

                <button type="submit" name="update_video" class="w-full bg-slate-900 text-white py-5 rounded-[1.8rem] font-black uppercase italic tracking-widest shadow-xl hover:bg-indigo-600 transition-all">
                    Update Recording <i class="fas fa-save ml-2"></i>
                </button>
            </form>
        </div>
    </main>

    <div id="statusModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-950/60 backdrop-blur-md transition-all">
        <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden" id="modalBox">
            <div class="p-8 text-center bg-slate-50 border-b border-slate-100"><h3 class="font-black text-slate-900 uppercase italic">Visibility Settings</h3></div>
            <div class="p-6 space-y-3">
                <button type="button" onclick="selectStatus('released', 'Normal (Released)')" class="w-full p-4 rounded-2xl border-2 border-slate-100 hover:border-emerald-500 text-left transition-all">
                    <span class="text-xs font-black uppercase">Normal</span>
                    <p class="text-[9px] text-slate-400 font-bold mt-1">Visible immediately.</p>
                </button>
                <button type="button" onclick="selectStatus('not_released', 'Unlisted (Hidden)')" class="w-full p-4 rounded-2xl border-2 border-slate-100 hover:border-rose-500 text-left transition-all">
                    <span class="text-xs font-black uppercase">Unlisted</span>
                    <p class="text-[9px] text-slate-400 font-bold mt-1">Hide from students.</p>
                </button>
                <div class="space-y-3">
                    <button type="button" onclick="togglePicker()" class="w-full p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-500 text-left transition-all">
                        <span class="text-xs font-black uppercase text-indigo-600">Change Expiry Date</span>
                    </button>
                    <div id="pickerDiv" class="<?= $recording['status'] == 'scheduled' ? '' : 'hidden' ?> px-2">
                        <input type="datetime-local" id="date_picker" value="<?= str_replace(' ', 'T', $recording['expire_date']) ?>" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none">
                    </div>
                </div>
            </div>
            <div class="p-6">
                <button type="button" onclick="closeModal()" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black uppercase text-xs">Confirm Changes</button>
            </div>
        </div>
    </div>
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
        const modal = document.getElementById('statusModal');
        function openModal() { modal.classList.replace('hidden', 'flex'); }
        function closeModal() {
            const pickerValue = document.getElementById('date_picker').value;
            if(pickerValue && !document.getElementById('pickerDiv').classList.contains('hidden')) {
                document.getElementById('form_status').value = 'scheduled';
                document.getElementById('form_expire_date').value = pickerValue;
                document.getElementById('status-label').innerText = "Status: Scheduled (" + pickerValue.replace('T', ' ') + ")";
            }
            modal.classList.replace('flex', 'hidden');
        }
        function selectStatus(status, label) {
            document.getElementById('form_status').value = status;
            document.getElementById('form_expire_date').value = "";
            document.getElementById('status-label').innerText = "Status: " + label;
            document.getElementById('pickerDiv').classList.add('hidden');
        }
        function togglePicker() { document.getElementById('pickerDiv').classList.toggle('hidden'); }
    </script>
</body>
</html>