<?php
session_start();
include 'config.php';

if (isset($_POST['student_id']) && isset($_POST['class_id'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $today = date('Y-m-d');

    // 1. මුලින්ම ශිෂ්‍යයා පද්ධතියේ ඉන්නවාද සහ පන්තියට register වෙලාද කියලා බලනවා
    $check_student = $conn->query("SELECT s.* FROM student s 
                                   JOIN student_classes sc ON s.id = sc.student_id 
                                   WHERE s.id = '$student_id' AND sc.class_id = '$class_id'");

    if ($check_student->num_rows > 0) {
        $student = $check_student->fetch_assoc();
        
        // 2. අද දිනට දැනටමත් Attendance mark කරලද බලනවා
        $check_attendance = $conn->query("SELECT * FROM attendance 
                                          WHERE student_id = '$student_id' 
                                          AND class_id = '$class_id' 
                                          AND date = '$today'");

        if ($check_attendance->num_rows > 0) {
            // දැනටමත් පැමිණීම සටහන් කර ඇත
            echo '
            <div class="bg-amber-50 border-2 border-amber-200 p-5 rounded-[2rem] text-center animate-pulse">
                <i class="fas fa-check-double text-amber-500 text-3xl mb-2"></i>
                <h3 class="font-black text-slate-800 uppercase italic">'.$student['full_name'].'</h3>
                <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mt-1">Already Marked for Today!</p>
            </div>';
        } else {
            // Attendance mark කරන්න පුළුවන් UI එක
            echo '
            <div class="bg-white border-2 border-indigo-100 p-5 rounded-[2rem] shadow-xl">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center font-black">
                        ID '.$student['id'].'
                    </div>
                    <div class="text-left">
                        <h3 class="font-black text-slate-800 uppercase italic leading-none">'.$student['full_name'].'</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 tracking-tighter">'.$student['town'].' | '.$student['contact_number'].'</p>
                    </div>
                </div>
                <button onclick="submitAttendance('.$student['id'].', '.$class_id.')" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-emerald-100 uppercase italic tracking-widest text-sm">
                    <i class="fas fa-fingerprint mr-2"></i> Mark Present
                </button>
            </div>';
        }
    } else {
        // ශිෂ්‍යයා පන්තියට register වී නැත
        echo '
        <div class="bg-rose-50 border-2 border-rose-100 p-6 rounded-[2rem] text-center">
            <i class="fas fa-user-slash text-rose-400 text-3xl mb-2"></i>
            <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Student Not Found in this Class!</p>
            <p class="text-[9px] font-bold text-slate-400 mt-1 uppercase">Please check the ID or Register first.</p>
        </div>';
    }
}
?>