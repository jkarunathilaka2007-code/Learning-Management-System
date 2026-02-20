<?php
session_start();
include 'config.php';

$paper_id = $_GET['paper_id'];
$paper_info = $conn->query("SELECT * FROM exam_papers WHERE paper_id = '$paper_id'")->fetch_assoc();
$class_id = $paper_info['class_id'];

// පන්තියේ සියලුම සිසුන් සහ ඔවුන්ගේ දැනට තියෙන ලකුණු ලබා ගැනීම
$sql = "SELECT s.id, s.full_name, m.marks_obtained 
        FROM student s 
        JOIN student_classes sc ON s.id = sc.student_id 
        LEFT JOIN student_marks m ON s.id = m.student_id AND m.paper_id = '$paper_id'
        WHERE sc.class_id = '$class_id'
        ORDER BY s.full_name ASC";
$students = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Marks - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 pb-20">
    <div class="bg-amber-500 text-white p-8 rounded-b-[3rem] shadow-lg">
        <h1 class="text-xl font-black uppercase italic">Edit Paper Marks</h1>
        <p class="text-xs font-bold opacity-80"><?= $paper_info['paper_name'] ?> (Max: <?= $paper_info['max_marks'] ?>)</p>
    </div>

    <main class="max-w-2xl mx-auto px-4 -mt-8">
        <form id="editMarksForm" class="bg-white p-6 rounded-[2.5rem] shadow-xl">
            <input type="hidden" name="paper_id" value="<?= $paper_id ?>">
            <input type="hidden" name="max_marks" value="<?= $paper_info['max_marks'] ?>">

            <div class="space-y-4">
                <?php while($s = $students->fetch_assoc()): ?>
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="w-1/2">
                        <h3 class="text-sm font-black text-slate-700 uppercase"><?= $s['full_name'] ?></h3>
                        <p class="text-[10px] font-bold text-slate-400">ID: <?= $s['id'] ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="setAbsent(<?= $s['id'] ?>)" class="bg-slate-200 text-slate-600 px-2 py-3 rounded-xl text-[9px] font-black uppercase">AB</button>
                        
                        <input type="number" step="0.01" 
                               name="marks[<?= $s['id'] ?>]" 
                               id="mark_<?= $s['id'] ?>"
                               value="<?= ($s['marks_obtained'] == -1) ? '' : $s['marks_obtained'] ?>"
                               placeholder="<?= ($s['marks_obtained'] == -1) ? 'AB' : '0.0' ?>"
                               class="w-20 p-3 rounded-xl border-2 border-slate-200 text-center font-black text-indigo-600 outline-none focus:border-indigo-500">
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <button type="button" onclick="updateMarks()" class="w-full mt-8 bg-indigo-600 text-white py-5 rounded-2xl font-black uppercase italic tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-100">
                Update All Marks
            </button>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function setAbsent(id) {
        // Absent නම් ලකුණු -1 ලෙස සලකමු
        document.getElementById('mark_' + id).value = -1;
        document.getElementById('mark_' + id).placeholder = 'AB';
    }

    function updateMarks() {
        const formData = $('#editMarksForm').serialize();
        $.ajax({
            url: 'update_marks_ajax.php',
            type: 'POST',
            data: formData,
            success: function(resp) {
                if(resp.trim() === 'success') {
                    alert('Marks Updated Successfully!');
                    window.location.href = 'view_marks_report.php?paper_id=<?= $paper_id ?>&class_id=<?= $class_id ?>';
                } else {
                    alert('Error: ' + resp);
                }
            }
        });
    }
    </script>
</body>
</html>