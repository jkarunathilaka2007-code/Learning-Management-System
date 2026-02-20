<?php
include 'config.php';

if(isset($_POST['paper_id'])) {
    $paper_id = mysqli_real_escape_string($conn, $_POST['paper_id']);
    
    // Top 3 Studentsla ganna query eka
    $query = "SELECT sm.marks_obtained, sm.percentage, s.full_name 
              FROM student_marks sm 
              JOIN student s ON sm.student_id = s.id 
              WHERE sm.paper_id = '$paper_id' 
              ORDER BY sm.marks_obtained DESC LIMIT 3";
              
    $result = $conn->query($query);
    
    if($result->num_rows > 0) {
        $rank = 1;
        while($row = $result->fetch_assoc()) {
            $color = ($rank == 1) ? 'amber-400' : (($rank == 2) ? 'slate-300' : 'orange-400');
            echo '<div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl mb-2 border border-slate-100">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 bg-'.$color.' rounded-full flex items-center justify-center text-white font-black text-xs shadow-sm">
                            '.$rank.'
                        </div>
                        <div>
                            <p class="text-[11px] font-black text-slate-800 uppercase tracking-tight">'.$row['full_name'].'</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase">Rank '.$rank.'</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-black text-slate-900">'.$row['marks_obtained'].' Marks</p>
                        <p class="text-[9px] font-bold text-indigo-500">'.$row['percentage'].'%</p>
                    </div>
                  </div>';
            $rank++;
        }
    } else {
        echo '<p class="text-center py-4 text-xs font-bold text-slate-400">No ranking data available.</p>';
    }
}
?>