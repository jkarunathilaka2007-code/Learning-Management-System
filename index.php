<?php
session_start();
include 'config.php';

// 1. Fetch Teacher Data
$teacher_res = $conn->query("SELECT name FROM teacher LIMIT 1");
$teacher = $teacher_res->fetch_assoc();
$teacher_name = $teacher['name'] ?? "Teacher Name";

// 2. Fetch About Data
$about_res = $conn->query("SELECT * FROM about LIMIT 1");
$about = $about_res->fetch_assoc();

// Decode JSON Data Safely
$timetable = json_decode($about['timetable'] ?? '[]', true);
if (!is_array($timetable)) $timetable = [];

$group_links = json_decode($about['group_links'] ?? '[]', true);
if (!is_array($group_links)) $group_links = [];

// 3. Profile Pic Path Logic for Logged User
$upload_path = "uploads/profile_pics/";
$is_logged_in = isset($_SESSION['user_id']);
$student_name = "Guest User";
$profile_img = "uploads/profile_pics/default-user.webp"; 

if ($is_logged_in) {
    $student_id = $_SESSION['user_id'];
    $user_q = "SELECT full_name, profile_pic FROM student WHERE id = '$student_id'";
    $user_res = $conn->query($user_q);
    if ($user_res && $user_res->num_rows > 0) {
        $row = $user_res->fetch_assoc();
        $student_name = $row['full_name'];
        if(!empty($row['profile_pic'])) $profile_img = $upload_path . $row['profile_pic'];
    }
}

$action = $is_logged_in ? "" : "onclick='restrictedAccess(event)'";

// Teacher Images from DB (Fallbacks handled)
$desk_banner = !empty($about['desktop_banner']) ? $about['desktop_banner'] : 'assets/img/default-desk-banner.jpg';
$mob_banner = !empty($about['mobile_banner']) ? $about['mobile_banner'] : 'assets/img/default-mob-banner.jpg';
$teacher_dp = !empty($about['profile_pic']) ? $about['profile_pic'] : 'assets/img/default-teacher.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $teacher_name ?> | Professional LMS</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        :root { --nav-dark: #0f172a; --accent: #6366f1; }
        body { 
            font-family: 'Inter', sans-serif; 
            /* Light to Dark Mix Gradient Background */
            background: linear-gradient(135deg, #e2e8f0 0%, #ffffff 50%, #94a3b8 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* --- PREMIUM SIDE NAV --- */
        .sidenav { 
            height: 100vh; width: 280px; position: fixed; z-index: 1100; top: 0; left: -280px; 
            background: linear-gradient(180deg, #020617 0%, #0f172a 60%, #1e1b4b 100%); 
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto;
            scrollbar-width: none; -ms-overflow-style: none;
        }
        .sidenav::-webkit-scrollbar { display: none; }
        .sidenav.active { left: 0; box-shadow: 20px 0 50px rgba(0,0,0,0.4); }
        .nav-links li a { padding: 15px 20px; color: #cbd5e1; display: flex; align-items: center; border-radius: 12px; text-decoration: none; margin-bottom: 8px; transition: 0.3s; }
        .nav-links li a:hover { background: rgba(255, 255, 255, 0.27); color: white; transform: translateX(8px); }

        /* --- TOP BAR & BANNERS --- */
        .top-bar { background: #0f172a; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; box-shadow: 0 4px 20px rgb(49, 20, 20); }
        .banner-section img { width: 100%; object-fit: cover; }
        .desktop-banner { height: 400px; } .mobile-banner { height: 250px; }
        @media (max-width: 768px) { .desktop-banner { display: none; } }
        @media (min-width: 769px) { .mobile-banner { display: none; } }

        /* --- MAIN DASHBOARD BUTTON CARDS (GLOWING DARK) --- */
        .scroll-container { display: flex; overflow-x: auto; gap: 18px; padding: 30px 25px; scrollbar-width: none; }
        .scroll-container::-webkit-scrollbar { display: none; }

        .nav-card { 
            min-width: 165px; background: #6366f1; padding: 30px 15px; border-radius: 28px; 
            text-align: center; border: 1px solid rgba(255, 255, 255, 0.08); 
            text-decoration: none !important; transition: 0.4s;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .nav-card i { font-size: 32px; color: #ffffff; margin-bottom: 15px; display: block; transition: 0.4s; }
        .nav-card span { font-size: 11.5px; font-weight: 700; color: #ffffff; text-transform: uppercase; letter-spacing: 1px; transition: 0.4s; }

        @media (min-width: 992px) {
            .nav-card { min-width: 210px; padding: 45px 20px; }
            .nav-card i { font-size: 40px; }
            .nav-card span { font-size: 13px; }
            .scroll-container { justify-content: center; flex-wrap: wrap; }
        }
        .nav-card:hover { transform: translateY(-10px); background: #0f172a; border-color: var(--accent); box-shadow: 0 20px 40px rgba(99, 102, 241, 0.25); }
        .nav-card:hover i { color: #ffffff; transform: scale(1.15) rotate(5deg); }
        .nav-card:hover span { color: #ffffff; }

        /* --- TEACHER BIO --- */
        .teacher-box { background-color: #c5b3b38a; margin: 0 25px 20px; padding: 40px; border-radius: 35px; border: 1px solid #edf2f7; display: flex; align-items: center; gap: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .teacher-img { width: 140px; height: 140px; border-radius: 35px; object-fit: cover; border: 4px solid #f8fafc; box-shadow: 0 10px 20px rgba(0,0,0,0.08); flex-shrink: 0;}
        @media (max-width: 768px) { .teacher-box { flex-direction: column; text-align: center; padding: 30px 20px;} }

        /* --- UPDATED TIMETABLE GRID --- */
        .timetable-section { background-color: #c5b3b38a; padding: 60px 25px; border-radius: 50px 50px 0 0; margin-top: 40px; box-shadow: 0 -10px 40px rgba(0,0,0,0.02); }
        .tt-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 30px;}
        .tt-card { 
            background: #ffffff; border: 1px solid #f1f5f9; padding: 25px; border-radius: 24px; 
            display: flex; gap: 20px; align-items: flex-start; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.02); position: relative; overflow: hidden;
        }
        .tt-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px; background: var(--accent); border-radius: 24px 0 0 24px;}
        .tt-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(99, 102, 241, 0.1); border-color: #e0e7ff; }
        .tt-icon { background: #e0e7ff; color: var(--accent); width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; }
        .tt-badge { background: #f1f5f9; color: #475569; padding: 5px 12px; border-radius: 10px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; display: inline-block; margin-bottom: 12px; }
        .tt-subject { font-size: 19px; font-weight: 800; color: #0f172a; margin-bottom: 10px; line-height: 1.3; }
        .tt-time { font-size: 13.5px; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 8px; }

        /* --- COMMUNITY GROUPS --- */
        .group-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 15px; padding: 0 25px 40px; background-color: #c5b3b38a;}
        .group-card { bbackground-color: #ffffff; padding: 18px; border-radius: 20px; border: 1px solid #e2e8f0; display: flex; align-items: center; text-decoration: none !important; transition: 0.3s;}
        .group-card:hover { background: white; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05);}
        .group-icon { width: 50px; height: 50px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: white; margin-right: 15px; }
        .whatsapp { background: #25D366; } .facebook { background: #1877F2; } .youtube { background: #FF0000; } .tiktok { background: #000000; } .instagram { background: #E1306C; }

        /* --- CONTACT CARDS --- */
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; padding: 0 25px 50px; background-color: #c5b3b38a;}
        .contact-card { background: #f8fafc; padding: 25px; border-radius: 24px; text-align: center; border: 1px solid #edf2f7; transition: 0.3s;}
        .contact-card:hover { background: white; box-shadow: 0 10px 20px rgba(0,0,0,0.04); }

        /* --- FOOTER --- */
        .final-footer { background: #0f172a; color: #94a3b8; padding: 60px 25px 30px; text-align: center; }

        .overlay { display: none; position: fixed; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); z-index: 1050; backdrop-filter: blur(4px); }
        .overlay.active { display: block; }
        .locked { opacity: 0.5; filter: grayscale(1); cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleNav()"></div>

    <nav class="sidenav" id="sidenav">
        <div class="p-5 text-center">
            <img src="<?= $profile_img ?>" class="rounded-circle border border-white mb-3 shadow-lg" width="90" height="90" style="object-fit:cover;">
            <div class="text-white font-weight-bold" style="font-size: 18px;"><?= $student_name ?></div>
            <small class="text-indigo-300 font-weight-bold uppercase" style="letter-spacing: 1px; font-size: 10px;">Student Portal</small>
            <?php if($is_logged_in): ?>
                <li><a href="logout.php" class="text-danger mt-5"><i class="fas fa-power-off mr-3"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="bg-primary text-white justify-content-center mt-4 rounded-xl font-weight-bold">Login Now</a></li>
            <?php endif; ?>
        </div>
        <ul class="nav-links list-unstyled px-3 mt-2">
            <li><a href="index.php"><i class="fas fa-home mr-3"></i> Home</a></li>
            <li><a href="student_page.php" <?= $action ?>><i class="fas fa-wallet mr-3"></i> Profile</a></li>
            <li><a href="st_notifications.php" <?= $action ?>><i class="fas fa-wallet mr-3"></i> Notifications</a></li>
            <li><a href="#" <?= $action ?>><i class="fas fa-wallet mr-3"></i> Informations</a></li>
            <li><a href="#" <?= $action ?>><i class="fas fa-wallet mr-3"></i> Contact</a></li>
            <li><a href="#" <?= $action ?>><i class="fas fa-wallet mr-3"></i> About</a></li>
            
        </ul>
    </nav>

    <div class="main-content">
        <header class="top-bar">
            <div class="menu-btn" onclick="toggleNav()" style="cursor:pointer; font-size: 30px; color: #ffffff;"><i class="fas fa-bars-staggered"></i></div>
            <div class="text-center">
                <h6 class="m-0 font-weight-bold" style="font-size: 15px; color: #ffffff;"><?= $teacher_name ?></h6>
                <small class="text-primary font-weight-bold" style="font-size: 9px; letter-spacing: 1.5px;">OFFICIAL LMS</small>
            </div>
            <div style="width:24px"></div>
        </header>

        <section class="banner-section">
            <img src="<?= $desk_banner ?>" class="desktop-banner" alt="Desktop Banner">
            <img src="<?= $mob_banner ?>" class="mobile-banner" alt="Mobile Banner">
        </section>

        <div class="scroll-container">
            <a href="st_live_classes.php" class="nav-card <?= $is_logged_in ? '' : 'locked' ?>" <?= $action ?>><i class="fas fa-chalkboard-teacher"></i><span>    Live Classes</span></a>
            <a href="st_recordings.php" class="nav-card <?= $is_logged_in ? '' : 'locked' ?>" <?= $action ?>><i class="fas fa-play-circle "></i><span>Recordings</span></a>
            <a href="st_score.php" class="nav-card <?= $is_logged_in ? '' : 'locked' ?>" <?= $action ?>><i class="fas fa-clipboard-check "></i><span>Paper Marks</span></a>
            <a href="st_p_papers.php" class="nav-card <?= $is_logged_in ? '' : 'locked' ?>" <?= $action ?>><i class="fas fa-file-pdf"></i><span>Past Papers</span></a>
            <a href="st_tutes.php" class="nav-card <?= $is_logged_in ? '' : 'locked' ?>" <?= $action ?>><i class="fas fa-book-reader "></i><span>Tutes</span></a>
        </div>

        <section class="teacher-box">
            <img src="<?= $teacher_dp ?>" class="teacher-img" alt="Teacher Profile">
            <div>
                <h3 class="font-weight-bold mb-2" style="color: #0f172a;"><?= $teacher_name ?></h3>
                <p class="text-muted m-0" style="font-size: 14.5px; line-height: 1.6;"><?= nl2br($about['bio'] ?? 'Welcome to our official Learning Management System.') ?></p>
            </div>
        </section>

        <section class="timetable-section">
            <div class="text-center mb-5">
                <h6 class="text-primary font-weight-bold uppercase" style="letter-spacing: 2px; font-size: 11px;">Schedule</h6>
                <h2 class="font-weight-bold" style="color: #0f172a;">Class Timetable</h2>
            </div>
            
            <div class="tt-grid">
                <?php if(!empty($timetable)): foreach($timetable as $row): ?>
                    <div class="tt-card">
                        <div class="tt-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div>
                            <div class="tt-badge"><?= $row['year'] ?> | <?= $row['stream'] ?></div>
                            <h5 class="tt-subject"><?= $row['subject'] ?></h5>
                            <div class="tt-time"><i class="far fa-clock text-primary"></i> <?= $row['day'] ?> &bull; <?= $row['time'] ?></div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="col-12 text-center text-muted p-4">No timetable published yet.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white">
            <div class="text-center mb-4 pt-4">
                <h2 class="font-weight-bold" style="color: #0f172a;">Join Our Community</h2>
            </div>
            <div class="group-grid">
                <?php foreach($group_links as $link): 
                    $media_class = strtolower($link['media']);
                    $icon_class = $media_class == 'tiktok' ? 'tiktok' : $media_class; 
                ?>
                    <a href="<?= $link['url'] ?>" target="_blank" class="group-card">
                        <div class="group-icon <?= $media_class ?>"><i class="fab fa-<?= $icon_class ?>"></i></div>
                        <div>
                            <div class="font-weight-bold text-dark" style="font-size: 14px;"><?= $link['town'] ?></div>
                            <div class="text-muted" style="font-size: 12px; font-weight: 600;"><?= $link['group_name'] ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="bg-white">
            <div class="contact-grid">
                <div class="contact-card"><i class="fas fa-map-marker-alt text-primary mb-3 fa-2x"></i><h6 class="font-weight-bold">Find Us</h6><small class="text-muted"><?= $about['address'] ?? 'Not Provided' ?></small></div>
                <div class="contact-card"><i class="fas fa-phone-alt text-primary mb-3 fa-2x"></i><h6 class="font-weight-bold">Call Us</h6><small class="text-muted"><?= $about['contact_number'] ?? 'Not Provided' ?></small></div>
                <div class="contact-card"><i class="fas fa-envelope text-primary mb-3 fa-2x"></i><h6 class="font-weight-bold">Mail Us</h6><small class="text-muted"><?= $about['email'] ?? 'Not Provided' ?></small></div>
            </div>
        </section>

        <footer class="final-footer">
            <h3 class="text-white font-weight-bold mb-3"><?= $teacher_name ?></h3>
            <p style="font-size: 13px;">Empowering students through quality education and modern technology. Join our community and excel in your studies.</p>
            <div class="mt-5 pt-4 border-top border-secondary" style="font-size: 12px;">&copy; <?= date('Y') ?> <?= $teacher_name ?> LMS. All Rights Reserved.</div>
        </footer>
    </div>

    <script>
        function toggleNav() {
            document.getElementById('sidenav').classList.toggle('active');
            document.querySelector('.overlay').classList.toggle('active');
        }
        function restrictedAccess(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Login Required',
                text: 'Please login to access this section.',
                icon: 'warning',
                confirmButtonColor: '#6366f1',
                confirmButtonText: 'Login Now'
            }).then((result) => { if (result.isConfirmed) window.location.href = "login.php"; });
        }
    </script>
</body>
</html>