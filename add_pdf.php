<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

// Class list dropdown
$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");

if (isset($_POST['submit'])) {
    $class_id  = mysqli_real_escape_string($conn, $_POST['class_id']);
    $pdf_title = mysqli_real_escape_string($conn, $_POST['pdf_title']);
    $sub_name  = mysqli_real_escape_string($conn, $_POST['sub_name']);
    $upload_type = $_POST['upload_type']; 
    
    $final_path = "";
    $success = false;

    if ($upload_type == 'file' && !empty($_FILES["pdf_file"]["name"])) {
        $target_dir = "uploads/pdf/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        $file_ext  = strtolower(pathinfo($_FILES["pdf_file"]["name"], PATHINFO_EXTENSION));
        $new_filename = "lms_pdf_" . time() . "_" . rand(100, 999) . "." . $file_ext;
        $target_file = $target_dir . $new_filename;

        if ($file_ext == "pdf") {
            if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $target_file)) {
                $final_path = $target_file;
                $success = true;
            } else { $message = "upload_error"; }
        } else { $message = "invalid_file"; }

    } else if ($upload_type == 'link') {
        $pdf_link = mysqli_real_escape_string($conn, $_POST['pdf_link']);
        if (!empty($pdf_link)) {
            $final_path = $pdf_link;
            $success = true;
        } else { $message = "link_empty"; }
    }

    if ($success) {
        $sql = "INSERT INTO pdf_files (class_id, pdf_title, sub_name, file_path) 
                VALUES ('$class_id', '$pdf_title', '$sub_name', '$final_path')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "success";
        } else { $message = "db_error"; }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add PDF - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%); min-height: 100vh; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid white; }
        .form-input { padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; outline: none; font-size: 13px; font-weight: 700; background: white; width: 100%; transition: 0.3s; }
        input[type="radio"]:checked + label { background-color: #0f172a; color: white; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="min-h-screen pb-28">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6 text-[10px] font-black uppercase tracking-widest">
                <a href="pdf.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back
                </a>
                <span class="opacity-50 italic">New Material</span>
            </div>
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Add Document</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Create or Link PDF Resources</p>
        </div>
    </div>

    <main class="max-w-2xl mx-auto px-4 -mt-16">
        <div class="glass-card shadow-2xl p-6 md:p-10">
            
            <?php if($message == "success"): ?>
                <div class="mb-6 p-4 bg-emerald-100/50 border border-emerald-200 text-emerald-700 rounded-2xl flex items-center gap-3 italic font-black text-[10px] uppercase animate-pulse">
                    <i class="fas fa-check-circle text-lg"></i> Upload Completed Successfully!
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-5">
                
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Target Class</label>
                    <select name="class_id" class="form-input" required>
                        <option value="">-- Choose Class --</option>
                        <?php while($c = $classes_list->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>"><?= $c['exam_year'] ?> | <?= $c['stream'] ?> | <?= $c['subject'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Title</label>
                        <input type="text" name="pdf_title" class="form-input" placeholder="Main Title" required>
                    </div>
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Sub Name</label>
                        <input type="text" name="sub_name" class="form-input" placeholder="Optional Topic">
                    </div>
                </div>

                <div class="flex gap-2 p-1 bg-slate-100 rounded-2xl">
                    <div class="flex-1">
                        <input type="radio" name="upload_type" id="type_file" value="file" class="hidden" checked onclick="toggleInput('file')">
                        <label for="type_file" class="block text-center py-3 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-pointer transition-all">
                            <i class="fas fa-file-upload mr-1"></i> File
                        </label>
                    </div>
                    <div class="flex-1">
                        <input type="radio" name="upload_type" id="type_link" value="link" class="hidden" onclick="toggleInput('link')">
                        <label for="type_link" class="block text-center py-3 rounded-xl text-[10px] font-black uppercase tracking-widest cursor-pointer transition-all">
                            <i class="fas fa-link mr-1"></i> Link
                        </label>
                    </div>
                </div>

                <div id="file_area" class="bg-slate-50 p-8 rounded-2xl border-2 border-dashed border-slate-200 text-center hover:border-indigo-400 transition-all">
                    <label class="cursor-pointer">
                        <i class="fas fa-cloud-arrow-up text-4xl text-slate-300 mb-2"></i>
                        <span class="block text-[9px] font-black text-slate-500 uppercase tracking-widest">Select PDF from Device</span>
                        <input type="file" name="pdf_file" class="hidden" accept=".pdf">
                    </label>
                </div>

                <div id="link_area" class="hidden">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1">Paste Cloud Link</label>
                    <input type="text" name="pdf_link" class="form-input" placeholder="https://drive.google.com/...">
                </div>

                <button type="submit" name="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-2xl transition-all active:scale-95">
                    Save to Database
                </button>

            </form>
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
        function toggleInput(type) {
            document.getElementById('file_area').classList.toggle('hidden', type !== 'file');
            document.getElementById('link_area').classList.toggle('hidden', type !== 'link');
        }
    </script>

</body>
</html>