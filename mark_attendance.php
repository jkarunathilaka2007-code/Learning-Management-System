<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];

if (isset($_POST['class_id'])) {
    $_SESSION['selected_class'] = $_POST['class_id'];
}
$selected_class = isset($_SESSION['selected_class']) ? $_SESSION['selected_class'] : '';

$classes = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mark Attendance - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 2.5rem; border: 1px solid white; }
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 65px;
            background: #0f172a; display: flex; justify-content: space-around;
            align-items: center; z-index: 1000;
        }
        input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="pb-32">

    <div class="bg-slate-900 text-white pb-24 pt-10 px-6 rounded-b-[3.5rem] shadow-2xl relative">
        <div class="max-w-xl mx-auto flex justify-between items-center">
            <a href="attendance.php" class="bg-white/10 hover:bg-white/20 p-3 rounded-2xl text-xs transition-all">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h1 class="text-xl font-black italic tracking-tighter uppercase leading-none">Mark<br>Attendance</h1>
            <div class="w-10"></div>
        </div>
    </div>

    <main class="max-w-xl mx-auto px-4 -mt-12">
        <div class="glass-card shadow-2xl p-6 md:p-8">
            
            <form method="POST" id="classForm" class="mb-8">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Step 1: Select Active Class</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full p-5 bg-slate-50 border-2 border-slate-100 rounded-3xl font-bold text-slate-700 outline-none focus:border-indigo-500 transition-all appearance-none">
                    <option value="">-- Choose Class --</option>
                    <?php while($c = $classes->fetch_assoc()): ?>
                        <option value="<?= $c['class_id'] ?>" <?= ($selected_class == $c['class_id']) ? 'selected' : '' ?>>
                            <?= $c['exam_year'] ?> <?= $c['stream'] ?> - <?= $c['subject'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>

            <?php if($selected_class): ?>
                <div class="mb-8">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Step 2: Scan or Enter Student ID</label>
                    <div class="flex gap-3">
                        <input type="number" id="student_id_input" class="w-full p-5 bg-white border-2 border-slate-200 rounded-3xl font-black text-lg text-indigo-600 outline-none focus:border-indigo-500 shadow-sm transition-all" placeholder="Enter ID (e.g. 1001)" autofocus>
                        <button onclick="findStudent()" class="bg-indigo-600 hover:bg-indigo-700 text-white w-20 rounded-3xl flex items-center justify-center active:scale-90 transition-all shadow-lg shadow-indigo-100">
                            <i class="fas fa-search text-xl"></i>
                        </button>
                    </div>
                </div>

                <div id="student_result" class="min-h-[120px] transition-all">
                    </div>

            <?php else: ?>
                <div class="text-center py-16 opacity-30">
                    <i class="fas fa-id-card-alt text-6xl mb-4"></i>
                    <p class="text-[11px] font-black uppercase tracking-[0.2em]">Select a class above to start marking</p>
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
        // 1. Find Student Details via AJAX
        function findStudent() {
            const studentId = document.getElementById('student_id_input').value;
            const classId = '<?= $selected_class ?>';

            if (!studentId) {
                alert('Please enter a valid Student ID');
                return;
            }

            $('#student_result').html('<div class="text-center py-6"><i class="fas fa-spinner fa-spin text-indigo-500"></i> Searching...</div>');

            $.ajax({
                url: 'fetch_student_attendance_ajax.php',
                type: 'POST',
                data: { student_id: studentId, class_id: classId },
                success: function(response) {
                    $('#student_result').html(response);
                },
                error: function() {
                    alert('Error connecting to the server. Please check your internet.');
                }
            });
        }

        // 2. Mark Final Attendance via AJAX
        function submitAttendance(studentId, classId) {
            $.ajax({
                url: 'save_single_attendance.php',
                type: 'POST',
                data: { 
                    student_id: studentId, 
                    class_id: classId 
                },
                success: function(response) {
                    if(response.trim() === 'success') {
                        $('#student_result').html(`
                            <div class="bg-emerald-500 text-white p-8 rounded-[2.5rem] text-center shadow-xl animate-pulse">
                                <i class="fas fa-check-circle text-4xl mb-3"></i>
                                <h2 class="text-lg font-black uppercase italic tracking-widest">Attendance Recorded!</h2>
                                <p class="text-[10px] font-bold opacity-80 mt-1 uppercase">Ready for next student</p>
                            </div>
                        `);
                        
                        // Clear input and focus back for next entry after 1.5s
                        setTimeout(() => {
                            $('#student_result').html('');
                            $('#student_id_input').val('').focus();
                        }, 1500);

                    } else if(response.trim() === 'exists') {
                        alert('Error: Attendance already recorded for this student today.');
                    } else {
                        alert('Server Error: ' + response);
                    }
                },
                error: function() {
                    alert('Could not save attendance. Try again.');
                }
            });
        }

        // 3. Support for Enter Key Press
        document.getElementById('student_id_input')?.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                findStudent();
            }
        });
    </script>

</body>
</html>