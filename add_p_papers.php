<?php
session_start();
include 'config.php';

// Teacher check (Optional)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Past Paper - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border-radius: 2rem; border: 1px solid white; }
        
        .form-input { 
            padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0; 
            outline: none; font-size: 13px; font-weight: 700; background: white;
            width: 100%; transition: 0.3s;
        }
        .form-input:focus { border-color: #6366f1; ring: 2px; ring-color: #6366f1; }
        
        .input-label {
            display: block; text-[9px] font-black text-slate-400 uppercase tracking-[0.15em] mb-2 ml-1;
        }

        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-28">

    <div class="bg-slate-900 text-white pb-28 pt-8 px-6 rounded-b-[3rem] shadow-2xl relative">
        <div class="max-w-5xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <a href="p_papers.php" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all">
                    <i class="fas fa-arrow-left text-indigo-400"></i> Back to List
                </a>
            </div>
            
            <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Add Paper</h1>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Upload Resources & Links</p>
        </div>
    </div>

    <main class="max-w-3xl mx-auto px-4 -mt-16 relative z-10">
        <div class="glass-card shadow-2xl p-6 md:p-10">
            
            <form action="process/save_paper.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <div>
                    <label class="input-label">Paper Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. 2023 Combined Maths I" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Exam Year</label>
                        <input type="text" name="year" class="form-input" placeholder="2023" required>
                    </div>
                    <div>
                        <label class="input-label">Category</label>
                        <select name="category" class="form-input">
                            <option value="Paper">Past Paper</option>
                            <option value="Marking">Marking Scheme</option>
                        </select>
                    </div>
                </div>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <label class="input-label text-indigo-600">File Source Method</label>
                    <select id="fileSource" name="file_source" class="form-input mb-4" onchange="toggleSource()">
                        <option value="upload">Upload PDF File</option>
                        <option value="link">Cloud Drive Link</option>
                    </select>

                    <div id="uploadDiv">
                        <label class="input-label">Select PDF</label>
                        <input type="file" name="paper_file" id="paper_file" class="text-xs font-bold text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer w-full" accept=".pdf">
                    </div>

                    <div id="linkDiv" class="hidden">
                        <label class="input-label text-emerald-600">Paste Link URL</label>
                        <input type="url" name="file_url" id="file_url" class="form-input border-emerald-100 focus:border-emerald-500" placeholder="https://drive.google.com/...">
                    </div>
                </div>

                <button type="submit" name="submit" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-[0.2em] hover:bg-indigo-600 shadow-xl shadow-indigo-500/20 transition-all active:scale-95">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Save & Publish
                </button>

            </form>
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

    <script>
        function toggleSource() {
            const source = document.getElementById('fileSource').value;
            const uploadDiv = document.getElementById('uploadDiv');
            const linkDiv = document.getElementById('linkDiv');
            const fileInput = document.getElementById('paper_file');
            const urlInput = document.getElementById('file_url');

            if (source === 'upload') {
                uploadDiv.classList.remove('hidden');
                linkDiv.classList.add('hidden');
                fileInput.required = true;
                urlInput.required = false;
            } else {
                uploadDiv.classList.add('hidden');
                linkDiv.classList.remove('hidden');
                fileInput.required = false;
                urlInput.required = true;
            }
        }
        // Initialize
        window.onload = toggleSource;
    </script>

    <?php
    if (isset($_GET['status'])) {
        $icon = ($_GET['status'] == 'success') ? 'success' : 'error';
        $title = ($_GET['status'] == 'success') ? 'Uploaded!' : 'Failed!';
        echo "<script>
            Swal.fire({
                title: '$title',
                icon: '$icon',
                background: '#fff',
                color: '#0f172a',
                confirmButtonColor: '#4f46e5',
                borderRadius: '20px'
            });
        </script>";
    }
    ?>

</body>
</html>