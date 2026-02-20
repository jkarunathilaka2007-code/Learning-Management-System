<?php
session_start();
include 'config.php';

// Teacher පරීක්ෂාව
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$msg = "";

// Form එක Submit වූ පසු
if (isset($_POST['add_video'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $video_title = mysqli_real_escape_string($conn, $_POST['video_title']);
    $sub_title = mysqli_real_escape_string($conn, $_POST['sub_title']);
    $raw_url = $_POST['video_url'];
    
    // Duration formatting (Ex: 01h 45m)
    $duration = $_POST['duration_hrs'] . "h " . $_POST['duration_mins'] . "m";
    
    // Folder Logic
    $folder_name = !empty($_POST['new_folder']) ? mysqli_real_escape_string($conn, $_POST['new_folder']) : mysqli_real_escape_string($conn, $_POST['folder_name']);
    
    // Visibility Logic
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $expire_date = !empty($_POST['expire_date']) ? "'".mysqli_real_escape_string($conn, $_POST['expire_date'])."'" : "NULL";

    // YouTube ID Extraction
    function getYouTubeId($url) {
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
        return isset($match[1]) ? $match[1] : null;
    }

    $video_id = getYouTubeId($raw_url);

    if ($video_id) {
        $sql = "INSERT INTO recordings (class_id, video_title, sub_title, video_id, folder_name, duration, status, expire_date) 
                VALUES ('$class_id', '$video_title', '$sub_title', '$video_id', '$folder_name', '$duration', '$status', $expire_date)";
        
        if ($conn->query($sql)) {
            $msg = "success";
        } else {
            $msg = "error";
        }
    } else {
        $msg = "invalid_url";
    }
}

// අවශ්‍ය Data ලබා ගැනීම
$classes = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
$existing_folders = $conn->query("SELECT DISTINCT folder_name FROM recordings WHERE class_id IN (SELECT class_id FROM classes WHERE teacher_id = '$teacher_id')");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Lesson - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .input-box { width: 100%; padding: 1rem; background: #fff; border: 2px solid #e2e8f0; border-radius: 1.2rem; font-weight: 600; outline: none; transition: 0.3s; }
        .input-box:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .section-label { font-size: 10px; font-weight: 900; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-left: 0.5rem; margin-bottom: 0.5rem; display: block; }
    </style>
</head>
<body class="pb-20">

    <header class="bg-slate-950 text-white pt-16 pb-32 px-6 rounded-b-[3.5rem] shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
        <div class="max-w-xl mx-auto flex justify-between items-center relative z-10">
            <a href="admin_page.php" class="w-11 h-11 flex items-center justify-center bg-white/10 rounded-2xl hover:bg-white/20 transition-all"><i class="fas fa-chevron-left"></i></a>
            <div class="text-center">
                <h1 class="text-xl font-black italic uppercase tracking-tighter leading-tight">Post New<br><span class="text-indigo-400 text-xs tracking-[0.3em]">Recording</span></h1>
            </div>
            <div class="w-11"></div>
        </div>
    </header>

    <main class="max-w-xl mx-auto px-4 -mt-20 relative z-20">
        
        <?php if($msg == "success"): ?>
            <div class="bg-emerald-500 text-white p-5 rounded-3xl mb-6 flex items-center gap-4 shadow-xl shadow-emerald-500/20">
                <i class="fas fa-check-double text-xl"></i>
                <p class="text-xs font-black uppercase">Lesson successfully published!</p>
            </div>
        <?php elseif($msg == "invalid_url"): ?>
            <div class="bg-rose-500 text-white p-5 rounded-3xl mb-6 flex items-center gap-4 shadow-xl shadow-rose-500/20">
                <i class="fas fa-exclamation-triangle text-xl"></i>
                <p class="text-xs font-black uppercase">Invalid YouTube URL. Check again.</p>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl shadow-slate-200 border border-white">
            <form action="" method="POST" class="space-y-6">
                
                <div>
                    <label class="section-label">01. Target Class</label>
                    <select name="class_id" required class="input-box appearance-none">
                        <option value="">-- Choose Class --</option>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>"><?= $c['exam_year'] ?> - <?= $c['subject'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="section-label">02. Select Folder</label>
                        <select name="folder_name" class="input-box">
                            <option value="General">General</option>
                            <?php while($f = $existing_folders->fetch_assoc()): ?>
                                <option value="<?= $f['folder_name'] ?>"><?= $f['folder_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="section-label">Or Create New</label>
                        <input type="text" name="new_folder" placeholder="Folder Name..." class="input-box border-dashed border-indigo-200">
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="section-label">03. Lesson Title</label>
                        <input type="text" name="video_title" required placeholder="Ex: Lesson 05" class="input-box">
                    </div>
                    <div>
                        <label class="section-label">Topic / Sub-title</label>
                        <input type="text" name="sub_title" placeholder="Ex: Part 01" class="input-box">
                    </div>
                </div>

                <div>
                    <label class="section-label">04. Video Duration for watch</label>
                    <div class="flex gap-3">
                        <select name="duration_hrs" class="input-box">
                            <?php for($i=0; $i<=10; $i++) echo "<option value='".sprintf("%02d", $i)."'>$i Hours</option>"; ?>
                        </select>
                        <select name="duration_mins" class="input-box">
                            <?php for($i=0; $i<=59; $i++) echo "<option value='".sprintf("%02d", $i)."'>$i Minutes</option>"; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="section-label">05. YouTube URL</label>
                    <input type="text" name="video_url" required placeholder="https://youtube.com/..." class="input-box border-indigo-50">
                </div>

                <div>
                    <label class="section-label">06. Visibility Control</label>
                    <button type="button" onclick="openModal()" class="w-full p-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-between group hover:border-indigo-500 transition-all">
                        <span id="status-label" class="text-xs font-black text-slate-500 uppercase tracking-widest"><i class="far fa-calendar-alt mr-2"></i> Normal (Released)</span>
                        <i class="fas fa-cog text-slate-300 group-hover:rotate-90 transition-all"></i>
                    </button>
                    <input type="hidden" name="status" id="form_status" value="released">
                    <input type="hidden" name="expire_date" id="form_expire_date" value="">
                </div>

                <button type="submit" name="add_video" class="w-full bg-indigo-600 text-white py-5 rounded-[1.8rem] font-black uppercase italic tracking-[0.2em] shadow-xl hover:bg-slate-900 transition-all active:scale-95">
                    Save Recording <i class="fas fa-cloud-upload-alt ml-2"></i>
                </button>

            </form>
        </div>
    </main>

    <div id="statusModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-6 bg-slate-950/60 backdrop-blur-md transition-all">
        <div class="bg-white w-full max-w-sm rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300" id="modalBox">
            <div class="p-8 text-center bg-slate-50 border-b border-slate-100">
                <h3 class="font-black text-slate-900 uppercase italic">Visibility Settings</h3>
            </div>
            
            <div class="p-6 space-y-3">
                <button type="button" onclick="selectStatus('released', 'Normal (Released)')" class="w-full p-4 rounded-2xl border-2 border-slate-100 hover:border-emerald-500 hover:bg-emerald-50 text-left transition-all">
                    <div class="flex justify-between items-center"><span class="text-xs font-black uppercase">Normal</span><i class="fas fa-globe text-emerald-500"></i></div>
                    <p class="text-[9px] text-slate-400 font-bold mt-1">Visible to all students immediately.</p>
                </button>

                <button type="button" onclick="selectStatus('not_released', 'Unlisted (Hidden)')" class="w-full p-4 rounded-2xl border-2 border-slate-100 hover:border-rose-500 hover:bg-rose-50 text-left transition-all">
                    <div class="flex justify-between items-center"><span class="text-xs font-black uppercase">Unlisted</span><i class="fas fa-eye-slash text-rose-500"></i></div>
                    <p class="text-[9px] text-slate-400 font-bold mt-1">Video will be hidden from the library.</p>
                </button>

                <div class="space-y-3">
                    <button type="button" onclick="toggleSchedulePicker()" class="w-full p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-500 hover:bg-indigo-50 text-left transition-all">
                        <div class="flex justify-between items-center"><span class="text-xs font-black uppercase">Schedule Expiry</span><i class="fas fa-clock text-indigo-500"></i></div>
                        <p class="text-[9px] text-slate-400 font-bold mt-1">Hide automatically after a date.</p>
                    </button>
                    <div id="pickerDiv" class="hidden px-2 animate-bounce">
                        <input type="datetime-local" id="date_picker" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none">
                    </div>
                </div>
            </div>

            <div class="p-6">
                <button type="button" onclick="closeModal()" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black uppercase text-xs">Apply Settings</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('statusModal');
        const modalBox = document.getElementById('modalBox');

        function openModal() {
            modal.classList.replace('hidden', 'flex');
            setTimeout(() => { modalBox.classList.replace('scale-95', 'scale-100'); modalBox.classList.replace('opacity-0', 'opacity-100'); }, 10);
        }

        function closeModal() {
            const pickerValue = document.getElementById('date_picker').value;
            if(pickerValue && !document.getElementById('pickerDiv').classList.contains('hidden')) {
                document.getElementById('form_status').value = 'scheduled';
                document.getElementById('form_expire_date').value = pickerValue;
                document.getElementById('status-label').innerHTML = `<i class="fas fa-calendar-check mr-2 text-indigo-500"></i> Expire: ${pickerValue.replace('T', ' ')}`;
            }
            modal.classList.replace('flex', 'hidden');
        }

        function selectStatus(status, label) {
            document.getElementById('form_status').value = status;
            document.getElementById('form_expire_date').value = "";
            document.getElementById('status-label').innerHTML = `<i class="fas fa-info-circle mr-2"></i> ${label}`;
            document.getElementById('pickerDiv').classList.add('hidden');
        }

        function toggleSchedulePicker() {
            document.getElementById('pickerDiv').classList.toggle('hidden');
        }
    </script>

</body>
</html>