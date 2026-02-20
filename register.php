<?php
include 'config.php';

$error = "";
$success = "";

// 1. ටවුන් සහ ආයතන විස්තර ලබා ගැනීම
$towns_res = $conn->query("SELECT * FROM class_towns ORDER BY town ASC");

// 2. පන්ති විස්තර ලබා ගැනීම
$classes_res = $conn->query("SELECT * FROM classes ORDER BY exam_year DESC");

if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $gmail = mysqli_real_escape_string($conn, $_POST['gmail']);
    $nic = mysqli_real_escape_string($conn, $_POST['nic_number']);
    $school = mysqli_real_escape_string($conn, $_POST['school']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $town_id = mysqli_real_escape_string($conn, $_POST['town']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $selected_classes = isset($_POST['classes']) ? $_POST['classes'] : [];
    $status = "pending"; // Default status for new students

    // Profile Pic Upload Process
    $profile_pic = "default_user.png";
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "uploads/profile_pics/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $profile_pic = time() . '_' . basename($_FILES['profile_pic']['name']);
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_dir . $profile_pic);
    }

    // Gmail එක කලින් පාවිච්චි කර ඇත්දැයි බැලීම
    $check_email = $conn->query("SELECT id FROM student WHERE gmail = '$gmail'");
    if ($check_email->num_rows > 0) {
        $error = "This Gmail is already registered! Please use another one.";
    } else {
        // SQL Transaction එකක් පාවිච්චි කිරීම (ආරක්ෂාව සඳහා)
        $conn->begin_transaction();

        try {
            // 3. ශිෂ්‍යයා ඇතුළත් කිරීම (Status = pending)
            $stmt = $conn->prepare("INSERT INTO student (full_name, gmail, nic_number, school, dob, address, contact_number, town, password, profile_pic, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssss", $full_name, $gmail, $nic, $school, $dob, $address, $contact, $town_id, $password, $profile_pic, $status);
            $stmt->execute();
            
            $student_id = $conn->insert_id;

            // 4. තෝරාගත් පන්ති ඇතුළත් කිරීම
            if (!empty($selected_classes)) {
                $stmt_class = $conn->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)");
                foreach ($selected_classes as $class_id) {
                    $stmt_class->bind_param("ii", $student_id, $class_id);
                    $stmt_class->execute();
                }
            }

            $conn->commit();
            $success = "Registration successful! Your account is pending teacher approval.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Something went wrong! " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        .form-card { background: rgba(255, 255, 255, 0.98); border-radius: 2.5rem; }
        .input-box { background: #f8fafc; border: 1px solid #e2e8f0; transition: all 0.2s; }
        .input-box:focus { border-color: #4f46e5; background: white; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); outline: none; }
        .class-checkbox:checked + label { border-color: #4f46e5; background: #f5f3ff; }
        .class-checkbox:checked + label .check-icon { display: block; }
    </style>
</head>
<body class="py-12 px-4">

    <div class="max-w-4xl mx-auto form-card p-8 md:p-12 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-bl-full"></div>

        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl text-white mb-4 shadow-lg shadow-indigo-200">
                <i class="fas fa-user-plus text-2xl"></i>
            </div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tighter uppercase italic">Student <span class="text-indigo-600">Register</span></h1>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mt-2">Fill the form below to join our LMS</p>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <div>
                <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center text-[10px]">1</span>
                    Personal Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Full Name</label>
                        <input type="text" name="full_name" required placeholder="Ex: Kamal Perera" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Gmail Address</label>
                        <input type="email" name="gmail" required placeholder="example@gmail.com" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">NIC Number</label>
                        <input type="text" name="nic_number" required placeholder="2001XXXXXXXX" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Date of Birth</label>
                        <input type="date" name="dob" required class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <span class="w-5 h-5 bg-indigo-100 rounded-full flex items-center justify-center text-[10px]">2</span>
                    Contact & School
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">School Name</label>
                        <input type="text" name="school" placeholder="Enter your school" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Town / Institute</label>
                        <select name="town" required class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                            <option value="">Select Location</option>
                            <?php while($t = $towns_res->fetch_assoc()): ?>
                                <option value="<?= $t['id'] ?>"><?= $t['town'] ?> (<?= $t['institute_name'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Contact Number</label>
                        <input type="text" name="contact_number" required placeholder="07XXXXXXXX" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Permanent Address</label>
                        <input type="text" name="address" required placeholder="Home address" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*" class="w-full mt-1.5 text-xs text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-indigo-600 file:text-white">
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Set Password</label>
                    <input type="password" name="password" required placeholder="••••••••" class="input-box w-full mt-1.5 px-4 py-3.5 rounded-2xl text-sm font-bold text-slate-700">
                </div>
            </div>

            <div class="pt-6">
                <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest mb-4">Select Your Classes</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <?php while($c = $classes_res->fetch_assoc()): ?>
                        <div class="relative">
                            <input type="checkbox" name="classes[]" value="<?= $c['class_id'] ?>" id="class_<?= $c['class_id'] ?>" class="hidden class-checkbox">
                            <label for="class_<?= $c['class_id'] ?>" class="flex items-center justify-between p-4 border border-slate-200 rounded-2xl cursor-pointer hover:border-indigo-300 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-book text-indigo-500 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black uppercase text-slate-800"><?= $c['subject'] ?></p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase"><?= $c['exam_year'] ?> • <?= $c['stream'] ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-indigo-600">Rs. <?= $c['class_fee'] ?></p>
                                    <p class="text-[7px] font-bold text-slate-300 uppercase"><?= $c['fee_type'] ?></p>
                                </div>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <button type="submit" name="register" class="w-full py-5 bg-slate-900 text-white rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] shadow-2xl hover:bg-indigo-600 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                Register Securely <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="text-center mt-8">
            <p class="text-slate-400 text-[10px] font-bold uppercase">Already have an account? <a href="login.php" class="text-indigo-600 underline">Sign In</a></p>
        </div>
    </div>

    <?php if($error != ""): ?>
        <script>Swal.fire({ icon: 'error', title: 'Registration Failed', text: '<?= $error ?>', confirmButtonColor: '#0f172a'});</script>
    <?php endif; ?>

    <?php if($success != ""): ?>
        <script>
            Swal.fire({ 
                icon: 'success', 
                title: 'SUCCESS!', 
                text: '<?= $success ?>', 
                confirmButtonColor: '#4f46e5'
            }).then(() => { window.location.href = 'login.php'; });
        </script>
    <?php endif; ?>

</body>
</html>