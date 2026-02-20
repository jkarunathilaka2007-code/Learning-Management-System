<?php
include 'config.php';

if(isset($_POST['student_id'])) {
    $sid = mysqli_real_escape_string($conn, $_POST['student_id']);
    $cid = mysqli_real_escape_string($conn, $_POST['class_id']);
    $today = date('Y-m-d');

    // 1. Balanawa student me class ekatada kiyala
    $sql = "SELECT s.* FROM student s 
            JOIN student_classes sc ON s.id = sc.student_id 
            WHERE s.id = '$sid' AND sc.class_id = '$cid'";
    
    $result = $conn->query($sql);

    if($row = $result->fetch_assoc()) {
        // 2. Balanawa ada dawase me student mark welada kiyala (Same student check)
        $check_dup = $conn->query("SELECT id FROM attendance WHERE student_id = '$sid' AND class_id = '$cid' AND date = '$today'");

        if($check_dup->num_rows > 0) {
            echo '<div class="p-5 bg-amber-50 rounded-3xl border border-amber-100 text-center animate-pulse">
                    <i class="fas fa-exclamation-circle text-amber-500 mb-2"></i>
                    <p class="text-amber-700 font-black text-[10px] uppercase">Already Marked for Today!</p>
                  </div>';
        } else {
            // automatic mark karanna puluwan natham confirm button ekak danna puluwan.
            // methana mama confirm button eka dennam confirm karama save wenna.
            ?>
            <div class="p-5 bg-white rounded-3xl border border-slate-100 shadow-lg text-center">
                <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full mx-auto flex items-center justify-center font-black text-xl mb-3">
                    <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                </div>
                <h3 class="text-sm font-black text-slate-800 uppercase italic"><?= $row['full_name'] ?></h3>
                <p class="text-[9px] font-bold text-slate-400 uppercase mt-1 mb-4 tracking-widest">ID: <?= $row['id'] ?></p>

                <form action="save_single_attendance.php" method="POST">
                    <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="class_id" value="<?= $cid ?>">
                    <button type="submit" name="mark_present" class="w-full py-4 bg-emerald-500 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-emerald-200 active:scale-95 transition-all">
                        Confirm Attendance <i class="fas fa-check-circle ml-1"></i>
                    </button>
                </form>
            </div>
            <?php
        }
    } else {
        echo '<div class="p-5 bg-rose-50 rounded-3xl border border-rose-100 text-center">
                <i class="fas fa-user-slash text-rose-400 mb-2"></i>
                <p class="text-rose-500 font-black text-[10px] uppercase">Student Not Enrolled!</p>
              </div>';
    }
}
?>