<?php
include 'config.php';

if(isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $month = isset($_GET['month']) ? $_GET['month'] : date('F');
    $year = isset($_GET['year']) ? $_GET['year'] : date('Y');

    $res = $conn->query("SELECT * FROM student WHERE id = '$id'");

    if($res && $res->num_rows > 0) {
        $student = $res->fetch_assoc();
        
        $query = "SELECT c.class_id, c.subject, c.class_fee, c.fee_type 
                  FROM classes c 
                  JOIN student_classes sc ON c.class_id = sc.class_id 
                  WHERE sc.student_id = '$id'";
                  
        $class_res = $conn->query($query);
        $classes = [];

        while($row = $class_res->fetch_assoc()) {
            $class_id = $row['class_id'];
            
            // 1. තෝරාගත් අවුරුද්දට අදාළ මාසික ගෙවීම්
            $m_res = $conn->query("SELECT DISTINCT payment_month FROM class_fees_payments WHERE student_id = '$id' AND class_id = '$class_id' AND YEAR(payment_date) = '$year'");
            $row['paid_months'] = [];
            while($m = $m_res->fetch_assoc()) { $row['paid_months'][] = $m['payment_month']; }

            // 2. තෝරාගත් අවුරුද්ද සහ මාසයට අදාළ දින
            $d_res = $conn->query("SELECT DISTINCT DAY(payment_date) as pay_day FROM class_fees_payments WHERE student_id = '$id' AND class_id = '$class_id' AND payment_month = '$month' AND YEAR(payment_date) = '$year'");
            $row['paid_days'] = [];
            while($d = $d_res->fetch_assoc()) { $row['paid_days'][] = (int)$d['pay_day']; }

            $classes[] = $row;
        }
        
        echo json_encode([
            'status' => 'success',
            'student' => ['name' => $student['full_name'], 'id' => $student['id']],
            'classes' => $classes
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    }
}