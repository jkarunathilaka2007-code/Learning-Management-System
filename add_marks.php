<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$classes = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");

$show_step2 = false;
$error_msg = '';
$selected_class_id = '';
$paper_name = '';
$max_marks = '';
$students = [];

if (isset($_POST['proceed_to_marks'])) {
    $selected_class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $paper_name = trim(mysqli_real_escape_string($conn, $_POST['paper_name']));
    $max_marks = mysqli_real_escape_string($conn, $_POST['max_marks']);

    // එකම පන්තියේ මේ නමින් දැනටමත් පේපර් එකක් තියෙනවද කියලා බලමු
    $check_duplicate = $conn->query("SELECT paper_id FROM exam_papers WHERE class_id = '$selected_class_id' AND paper_name = '$paper_name'");

    if ($check_duplicate->num_rows > 0) {
        $error_msg = "A paper with the name '$paper_name' already exists in this class!";
        $show_step2 = false; // ආපහු Step 1 එකම පෙන්වයි
    } else {
        $show_step2 = true;
        // අදාළ පන්තියේ ශිෂ්‍යයන්ව ලබා ගැනීම
        $students = $conn->query("SELECT s.id, s.full_name FROM student s 
                                   JOIN student_classes sc ON s.id = sc.student_id 
                                   WHERE sc.class_id = '$selected_class_id' 
                                   ORDER BY s.full_name ASC");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Exam Marks - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-header { background: #0f172a; border-radius: 0 0 3.5rem 3.5rem; }
        .card { background: white; border-radius: 2.5rem; border: 1px solid #f1f5f9; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
        .input-style { width: 100%; padding: 1rem 1.25rem; border-radius: 1.5rem; border: 2px solid #f1f5f9; font-weight: 800; outline: none; transition: 0.3s; background: #f8fafc; }
        .input-style:focus { border-color: #6366f1; background: white; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 60px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
    </style>
</head>
<body class="pb-32">

    <header class="glass-header text-white pt-12 pb-28 px-6 shadow-2xl relative z-10">
        <div class="max-w-3xl mx-auto flex justify-between items-center">
            <a href="admin_page.php" class="bg-white/10 p-3 rounded-2xl hover:bg-white/20 transition-all"><i class="fas fa-arrow-left"></i></a>
            <div class="text-center">
                <h1 class="text-2xl font-black uppercase italic tracking-tighter leading-none">Add Exam<br>Marks</h1>
            </div>
            <div class="w-10"></div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 -mt-16 relative z-20">
        
        <?php if ($error_msg): ?>
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-2xl mb-6 flex items-center gap-3 animate-fade-in shadow-sm">
                <i class="fas fa-exclamation-circle text-rose-500 text-xl"></i>
                <p class="text-xs font-bold text-rose-700 uppercase tracking-wide"><?= $error_msg ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$show_step2): ?>
        <div class="card p-8 animate-fade-in">
            <div class="flex items-center gap-3 mb-8">
                <span class="w-8 h-8 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-black text-xs shadow-inner">1</span>
                <h2 class="font-black uppercase italic text-slate-800 tracking-widest">Exam Setup</h2>
            </div>

            <form action="" method="POST" class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-2 tracking-widest">Target Class</label>
                    <select name="class_id" required class="input-style text-slate-700">
                        <option value="">-- Choose Class --</option>
                        <?php while($c = $classes->fetch_assoc()): ?>
                            <option value="<?= $c['class_id'] ?>" <?= ($selected_class_id == $c['class_id']) ? 'selected' : '' ?>>
                                <?= $c['exam_year'] ?> - <?= $c['subject'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-2 tracking-widest">Paper Name (Unique)</label>
                    <input type="text" name="paper_name" value="<?= $paper_name ?>" required placeholder="Ex: Unit Test 01 - Algebra" class="input-style text-slate-800">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-2 tracking-widest">Maximum Marks</label>
                    <input type="number" name="max_marks" value="<?= $max_marks ?: '100' ?>" required min="1" class="input-style text-indigo-600">
                </div>

                <button type="submit" name="proceed_to_marks" class="w-full bg-indigo-600 text-white py-5 rounded-[1.5rem] font-black uppercase italic tracking-widest hover:bg-indigo-700 active:scale-95 transition-all shadow-xl shadow-indigo-200 mt-4 flex justify-center items-center gap-2">
                    Proceed To Marks <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>

        <?php else: ?>
        <div class="card p-6 md:p-8 animate-fade-in">
            <div class="flex justify-between items-start mb-6 border-b border-slate-100 pb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest shadow-inner">Step 2</span>
                    </div>
                    <h2 class="font-black text-xl text-slate-800 uppercase italic leading-tight"><?= $paper_name ?></h2>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Max Marks: <span class="text-indigo-500"><?= $max_marks ?></span></p>
                </div>
                <a href="add_marks.php" class="text-[9px] font-black text-rose-500 uppercase bg-rose-50 px-3 py-2 rounded-xl hover:bg-rose-100 transition-colors">Change Setup</a>
            </div>

            <div class="mb-6 relative">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" onkeyup="filterStudents()" placeholder="Search Student by ID or Name..." 
                       class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] font-bold text-sm text-slate-700 outline-none focus:border-indigo-400 transition-all shadow-inner">
            </div>

            <form id="marksForm">
                <input type="hidden" name="class_id" value="<?= $selected_class_id ?>">
                <input type="hidden" name="paper_name" value="<?= $paper_name ?>">
                <input type="hidden" name="max_marks" value="<?= $max_marks ?>">

                <div class="space-y-3" id="studentsContainer">
                    <?php if ($students->num_rows > 0): ?>
                        <?php while($s = $students->fetch_assoc()): ?>
                        <div class="student-row flex items-center justify-between p-4 bg-white rounded-[1.5rem] border border-slate-100 shadow-sm hover:shadow-md transition-shadow" data-search="<?= strtolower($s['id'] . ' ' . $s['full_name']) ?>">
                            <div class="max-w-[55%]">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">ID: <?= $s['id'] ?></p>
                                <h3 class="text-xs font-black text-slate-700 uppercase truncate mt-0.5"><?= $s['full_name'] ?></h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="setAbsent(<?= $s['id'] ?>)" class="bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 px-3 py-3 rounded-xl text-[10px] font-black uppercase transition-colors shadow-inner">AB</button>
                                
                                <input type="number" step="0.01" 
                                       name="marks[<?= $s['id'] ?>]" 
                                       id="mark_<?= $s['id'] ?>"
                                       max="<?= $max_marks ?>" min="-1" 
                                       placeholder="0.0"
                                       class="w-20 p-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-center font-black text-indigo-600 outline-none focus:border-indigo-500 focus:bg-white transition-all">
                            </div>
                        </div>
                        <?php endwhile; ?>
                        
                        <div id="noResultsMsg" class="hidden text-center py-8">
                            <i class="fas fa-search-minus text-3xl text-slate-300 mb-2"></i>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No matching students found</p>
                        </div>

                        <button type="button" onclick="saveAllMarks()" class="w-full mt-8 bg-emerald-500 text-white py-5 rounded-[1.5rem] font-black uppercase italic tracking-widest hover:bg-emerald-600 active:scale-95 transition-all shadow-xl shadow-emerald-200/50 flex justify-center items-center gap-2">
                            <i class="fas fa-cloud-upload-alt text-lg"></i> Save Paper & Marks
                        </button>
                    <?php else: ?>
                        <div class="text-center py-12 bg-slate-50 rounded-[2rem]">
                            <i class="fas fa-users-slash text-4xl text-slate-300 mb-3"></i>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">No students in this class.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php endif; ?>

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Live Search Functionality
    function filterStudents() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.student-row');
        let visibleCount = 0;

        rows.forEach(row => {
            // data-search attribute එකේ තියෙන text එක බලලා filter කරනවා
            const searchData = row.getAttribute('data-search');
            if (searchData.includes(input)) {
                row.style.display = 'flex';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Search කරලා මුකුත් හම්බුනේ නැත්නම් message එකක් පෙන්වනවා
        document.getElementById('noResultsMsg').style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
    }

    // Quick Absent Button (-1)
    function setAbsent(id) {
        const input = document.getElementById('mark_' + id);
        input.value = -1;
        input.placeholder = 'AB';
        // පොඩි Visual Effect එකක් දෙනවා Absent කලාම
        input.classList.add('border-rose-300', 'text-rose-600', 'bg-rose-50');
        input.classList.remove('border-slate-100', 'text-indigo-600', 'bg-slate-50');
    }

    // AJAX Save Function
    function saveAllMarks() {
        const formData = $('#marksForm').serialize();
        const btn = event.currentTarget; // event.target වෙනුවට currentTarget යෙදීම ආරක්ෂිතයි
        
        // Button Loading State
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i> Saving Data...';
        btn.classList.add('opacity-80', 'cursor-not-allowed');

        $.ajax({
            url: 'save_paper_with_marks_ajax.php',
            type: 'POST',
            data: formData,
            success: function(resp) {
                if(resp.trim() === 'success') {
                    // Save වුණාට පස්සේ කෙලින්ම Report එකට යනවා
                    const classId = document.querySelector('input[name="class_id"]').value;
                    window.location.href = 'view_marks_report.php?class_id=' + classId;
                } else {
                    alert('Error: ' + resp);
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                }
            },
            error: function() {
                alert('Connection Error! Please try again.');
                btn.disabled = false;
                btn.innerHTML = originalContent;
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        });
    }
    </script>
</body>
</html>