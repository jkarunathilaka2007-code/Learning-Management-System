<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Setup - LMS Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white w-full max-w-md p-8 rounded-[2.5rem] shadow-xl shadow-slate-200 border border-slate-100">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-200">
                <i class="fas fa-user-tie text-2xl"></i>
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Teacher Setup</h2>
            <p class="text-slate-500 text-sm font-medium">Create your admin account</p>
        </div>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="text-xs font-bold text-slate-400 ml-2 uppercase tracking-wide">Full Name</label>
                <input type="text" name="name" required class="w-full mt-1 p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Enter your name">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 ml-2 uppercase tracking-wide">Gmail Address</label>
                <input type="email" name="gmail" required class="w-full mt-1 p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="example@gmail.com">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-400 ml-2 uppercase tracking-wide">Password</label>
                <input type="password" name="password" required class="w-full mt-1 p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="••••••••">
            </div>

            <button type="submit" name="register" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition-all">
                Complete Setup
            </button>
        </form>

        <?php
        include 'config.php';
        
        if(isset($_POST['register'])){
            // SQL Injection වලින් ආරක්ෂා වීමට දත්ත පිරිසිදු කිරීම
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $gmail = mysqli_real_escape_string($conn, $_POST['gmail']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Gmail එක දැනටමත් තියෙනවාදැයි බැලීම
            $check_email = "SELECT * FROM teacher WHERE gmail = '$gmail'";
            $res = $conn->query($check_email);

            if($res->num_rows > 0){
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'This Gmail is already registered!',
                        confirmButtonColor: '#4f46e5'
                    });
                </script>";
            } else {
                $sql = "INSERT INTO teacher (name, gmail, password) VALUES ('$name', '$gmail', '$password')";

                if ($conn->query($sql) === TRUE) {
                    // සාර්ථක මැසේජ් එක සහ Redirect logic එක
                    echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful!',
                            text: 'Welcome, Sir! Redirecting to login...',
                            timer: 3000,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        }).then(function() {
                            window.location = 'login.php';
                        });
                    </script>";
                } else {
                    echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Database Error',
                            text: 'Something went wrong. Please try again.',
                        });
                    </script>";
                }
            }
        }
        ?>
    </div>

</body>
</html>