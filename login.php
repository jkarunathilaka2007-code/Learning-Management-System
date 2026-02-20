<?php
session_start();
include 'config.php';

$error = "";

if (isset($_POST['login'])) {
    $gmail = mysqli_real_escape_string($conn, $_POST['gmail']);
    $password = $_POST['password'];

    // 1. Teacher Table Check (No changes made to redirection)
    $teacher_query = "SELECT * FROM teacher WHERE gmail = '$gmail'";
    $teacher_res = $conn->query($teacher_query);

    if ($teacher_res->num_rows > 0) {
        $user = $teacher_res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'teacher';
            $_SESSION['user_name'] = $user['name'];
            header("Location: admin_page.php");
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        // 2. Student Table Check (Added Status Verification)
        $student_query = "SELECT * FROM student WHERE gmail = '$gmail'";
        $student_res = $conn->query($student_query);

        if ($student_res && $student_res->num_rows > 0) {
            $user = $student_res->fetch_assoc();

            // මුලින්ම පරීක්ෂා කරන්නේ ශිෂ්‍යයා Approve කරලද කියලයි
            if ($user['status'] !== 'active') {
                $error = "Your account is still pending approval! Please contact your teacher.";
            } 
            // පසුව Password එක පරීක්ෂා කරයි
            else if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['user_name'] = $user['full_name'];
                header("Location: student_page.php");
                exit();
            } else {
                $error = "Invalid Password!";
            }
        } else {
            $error = "No account found with this Gmail!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login - LMS Pro</title>
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
        .glass-login { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .input-focus:focus-within {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">

    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10">
        <div class="absolute top-[-10%] right-[-10%] w-[400px] h-[400px] bg-indigo-600/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[400px] h-[400px] bg-indigo-900/40 rounded-full blur-[100px]"></div>
    </div>

    <div class="glass-login w-full max-w-[400px] p-8 rounded-[2.5rem] shadow-2xl">
        
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-slate-900 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                <i class="fas fa-shield-halved text-2xl text-indigo-400"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tighter uppercase italic">Secure <span class="text-indigo-600">Access</span></h2>
            <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] mt-1">LMS Pro Management System</p>
        </div>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Gmail Address</label>
                <div class="relative mt-1.5 input-focus border border-slate-200 rounded-2xl transition-all overflow-hidden bg-slate-50">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="email" name="gmail" required 
                           class="w-full pl-12 pr-4 py-4 bg-transparent border-none text-sm font-bold text-slate-700 outline-none placeholder:text-slate-300" 
                           placeholder="yourname@gmail.com">
                </div>
            </div>

            <div>
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Secret Password</label>
                <div class="relative mt-1.5 input-focus border border-slate-200 rounded-2xl transition-all overflow-hidden bg-slate-50">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="password" name="password" required 
                           class="w-full pl-12 pr-4 py-4 bg-transparent border-none text-sm font-bold text-slate-700 outline-none placeholder:text-slate-300" 
                           placeholder="••••••••">
                </div>
            </div>

            <button type="submit" name="login" 
                    class="w-full py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-900/20 hover:bg-indigo-600 active:scale-95 transition-all flex items-center justify-center gap-3">
                LOGIN TO DASHBOARD
                <i class="fas fa-chevron-right text-[10px]"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100">
            <div class="flex flex-col gap-3 text-center">
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-tight">
                    Don't have an account? 
                    <a href="register.php" class="text-indigo-600 hover:underline">Register Now</a>
                </p>
                <a href="#" class="text-slate-300 text-[9px] font-bold uppercase hover:text-slate-500 transition-colors">Forgot Password?</a>
            </div>
        </div>
    </div>

    <?php if($error != ""): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'ACCESS DENIED',
            text: '<?php echo $error; ?>',
            confirmButtonColor: '#0f172a',
            background: '#ffffff',
            customClass: {
                popup: 'rounded-[2rem] border-4 border-rose-500',
                title: 'font-black italic text-rose-600',
                confirmButton: 'rounded-xl font-bold uppercase px-8 py-3'
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>