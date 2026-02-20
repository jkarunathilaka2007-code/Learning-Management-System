<?php
session_start();
include 'config.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'teacher') {
    header("Location: login.php");
    exit();
}

$teacher_id = $_SESSION['user_id'];
$message = "";

// පන්ති සහ ටවුන් දත්ත ලබා ගැනීම
$classes_list = $conn->query("SELECT * FROM classes WHERE teacher_id = '$teacher_id' ORDER BY exam_year DESC");
$towns_list = $conn->query("SELECT DISTINCT town FROM class_towns WHERE teacher_id = '$teacher_id'");

if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $gmail = mysqli_real_escape_string($conn, $_POST['gmail']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $town = mysqli_real_escape_string($conn, $_POST['town']);
    $selected_classes = isset($_POST['classes']) ? $_POST['classes'] : [];
    
    $raw_password = '123456789'; 
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT); 

    $check_email = "SELECT gmail FROM student WHERE gmail = '$gmail'";
    $result = $conn->query($check_email);

    if ($result->num_rows > 0) {
        $message = "<div class='bg-rose-500/10 text-rose-500 p-4 rounded-2xl mb-6 font-bold text-center border border-rose-500/20 text-xs'>⚠️ Gmail already exists!</div>";
    } else {
        $sql = "INSERT INTO student (full_name, gmail, contact_number, town, password) 
                VALUES ('$full_name', '$gmail', '$contact', '$town', '$hashed_password')";

        if ($conn->query($sql) === TRUE) {
            $new_student_id = $conn->insert_id; 

            if (!empty($selected_classes)) {
                foreach ($selected_classes as $class_id) {
                    $class_id = mysqli_real_escape_string($conn, $class_id);
                    $conn->query("INSERT INTO student_classes (student_id, class_id) VALUES ('$new_student_id', '$class_id')");
                }
            }
            $message = "<div class='bg-emerald-500/10 text-emerald-600 p-4 rounded-2xl mb-6 font-bold text-center border border-emerald-500/20 text-xs'>✅ Registration Successful!</div>";
        } else {
            $message = "<div class='bg-rose-500/10 text-rose-500 p-4 rounded-2xl mb-6 font-bold text-center text-xs'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Student - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .glass-card { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.6); 
            border-radius: 2.5rem; 
        }
        .form-input { 
            width: 100%; padding: 14px 20px; border-radius: 1.25rem; 
            border: 1px solid #e2e8f0; outline: none; transition: all 0.3s; 
            background: rgba(255, 255, 255, 0.9); font-size: 14px;
        }
        .form-input:focus { border-color: #6366f1; box-shadow: 0 10px 25px -5px rgba(99,102,241,0.15); }
        
        .class-checkbox:checked + label {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen pb-24">

    <div class="bg-slate-900 text-white pb-24 pt-8 px-6 rounded-b-[3rem] shadow-2xl">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
            <div>
                <h1 class="text-3xl font-black italic tracking-tighter uppercase leading-none">Enroll Student</h1>
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] mt-2">Registration & Class Assigning</p>
            </div>
            <a href="admin_page.php" class="bg-white/10 hover:bg-white/20 px-6 py-3 rounded-2xl transition-all border border-white/10 text-[10px] font-black uppercase tracking-widest flex items-center gap-3">
                <i class="fas fa-arrow-left text-indigo-400"></i> Back to Dash
            </a>
        </div>
    </div>

    <main class="max-w-3xl mx-auto px-5 -mt-12">
        <div class="glass-card shadow-2xl shadow-slate-200/60 p-8 md:p-12">
            
            <?php echo $message; ?>

            <form action="" method="POST" class="space-y-8">
                
                <div>
                    <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-user-circle text-lg"></i> Student Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                            <input type="text" name="full_name" class="form-input" placeholder="e.g. Kasun Kalhara" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Gmail Address</label>
                            <input type="email" name="gmail" class="form-input" placeholder="example@gmail.com" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Contact Number</label>
                            <input type="text" name="contact_number" class="form-input" placeholder="07xxxxxxxx" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Town / Location</label>
                            <select name="town" class="form-input font-bold text-slate-700 cursor-pointer">
                                <option value="">Select a town</option>
                                <?php while($t = $towns_list->fetch_assoc()): ?>
                                    <option value="<?= $t['town'] ?>"><?= $t['town'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i class="fas fa-layer-group text-lg"></i> Assign Classes
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php if($classes_list->num_rows > 0): ?>
                            <?php while($c = $classes_list->fetch_assoc()): ?>
                                <div class="relative">
                                    <input type="checkbox" name="classes[]" value="<?= $c['class_id'] ?>" id="cls_<?= $c['class_id'] ?>" class="hidden class-checkbox">
                                    <label for="cls_<?= $c['class_id'] ?>" class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl cursor-pointer transition-all hover:border-indigo-300 shadow-sm">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-black text-slate-800 uppercase tracking-tight">
                                                <?= $c['exam_year'] ?> <?= $c['stream'] ?>
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                <?= $c['subject'] ?>
                                            </span>
                                        </div>
                                        <i class="fas fa-plus text-[10px] opacity-20"></i>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-2 p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center text-slate-400 font-bold text-[10px] uppercase tracking-widest">
                                No classes found. Create a class first.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="p-5 bg-indigo-50 border-l-4 border-indigo-500 rounded-r-2xl flex items-center gap-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-indigo-500 shadow-sm">
                        <i class="fas fa-key text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Access Credentials</p>
                        <p class="text-[11px] font-bold text-slate-600">Default Password: <span class="text-indigo-600 underline">123456789</span></p>
                    </div>
                </div>

                <button type="submit" name="register" class="w-full py-5 bg-slate-900 text-white rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.3em] hover:bg-black shadow-2xl transition-all active:scale-[0.98]">
                    Confirm Enrollment
                </button>
            </form>
        </div>
    </main>

    <nav class="fixed bottom-0 left-0 w-full h-20 bg-slate-900 flex justify-around items-center z-50 lg:hidden rounded-t-[2.5rem] border-t border-white/5">
        <a href="admin_page.php" class="text-slate-500 flex flex-col items-center group">
            <i class="fas fa-th-large text-xl transition-all group-hover:scale-110"></i>
            <span class="text-[9px] uppercase font-black mt-1 tracking-tighter">Dash</span>
        </a>
        <a href="student_reg.php" class="text-indigo-400 flex flex-col items-center group">
            <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center -mt-8 shadow-xl border border-white/10 transition-all group-hover:bg-indigo-600">
                <i class="fas fa-user-plus text-xl text-white"></i>
            </div>
            <span class="text-[9px] uppercase font-black mt-1 tracking-tighter">Register</span>
        </a>
        <a href="student_info.php" class="text-slate-500 flex flex-col items-center group">
            <i class="fas fa-users text-xl transition-all group-hover:scale-110"></i>
            <span class="text-[9px] uppercase font-black mt-1 tracking-tighter">Students</span>
        </a>
    </nav>

</body>
</html>