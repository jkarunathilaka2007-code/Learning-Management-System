<?php
session_start();
include 'config.php';

// Teacher ගේ ID එක (session එකේ නැත්නම් check කරන්න)
$teacher_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$data = json_decode(file_get_contents('php://input'), true);

if($data && !empty($data['classes'])) {
    $student_id = $data['student_id'];
    $month = $data['month'];
    $success_count = 0;

    foreach($data['classes'] as $cls) {
        $class_id = $cls['id'];
        $amount = $cls['fee'];
        
        // මෙතන table name සහ column names ඔයාගේ database එකට අනුව තියෙනවද බලන්න
        $sql = "INSERT INTO class_fees_payments (student_id, class_id, teacher_id, amount, payment_month) 
                VALUES ('$student_id', '$class_id', '$teacher_id', '$amount', '$month')";
        
        if($conn->query($sql)) {
            $success_count++;
        }
    }
    
    if($success_count > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Payments saved: ' . $success_count]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}
?>