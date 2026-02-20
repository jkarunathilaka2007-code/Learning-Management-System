<?php
session_start();
include 'config.php';

// --- 1. Form Submit කළ පසු Data Update කිරීම ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $conn->real_escape_string($_POST['bio']);
    $address = $conn->real_escape_string($_POST['address']);
    $contact_number = $conn->real_escape_string($_POST['contact_number']);
    $email = $conn->real_escape_string($_POST['email']);

    // දැනට තියෙන දත්ත ලබා ගැනීම (Image paths overwrite නොවී තියාගන්න)
    $current_res = $conn->query("SELECT * FROM about LIMIT 1");
    $current_data = $current_res->fetch_assoc();

    // Image Upload Function
    function uploadImage($fileKey, $existingPath) {
        if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == 0) {
            $dir = "uploads/about/";
            if (!is_dir($dir)) mkdir($dir, 0777, true); // Folder එක නැත්නම් හදනවා
            $fileName = time() . '_' . basename($_FILES[$fileKey]['name']);
            $targetFilePath = $dir . $fileName;
            if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFilePath)) {
                return $targetFilePath;
            }
        }
        return $existingPath;
    }

    $desktop_banner = uploadImage('desktop_banner', $current_data['desktop_banner'] ?? '');
    $mobile_banner = uploadImage('mobile_banner', $current_data['mobile_banner'] ?? '');
    $profile_pic = uploadImage('profile_pic', $current_data['profile_pic'] ?? '');

    // Timetable Data Array එකක් විදිහට සකසා JSON කිරීම
    $timetableArr = [];
    if (isset($_POST['year'])) {
        for ($i = 0; $i < count($_POST['year']); $i++) {
            if (!empty($_POST['year'][$i]) || !empty($_POST['subject'][$i])) {
                $timetableArr[] = [
                    'year' => $conn->real_escape_string($_POST['year'][$i]),
                    'stream' => $conn->real_escape_string($_POST['stream'][$i]),
                    'subject' => $conn->real_escape_string($_POST['subject'][$i]),
                    'day' => $conn->real_escape_string($_POST['day'][$i]),
                    'time' => $conn->real_escape_string($_POST['time'][$i])
                ];
            }
        }
    }
    $timetable_json = json_encode($timetableArr);

    // Group Links Array එකක් විදිහට සකසා JSON කිරීම
    $groupLinksArr = [];
    if (isset($_POST['media_type'])) {
        for ($i = 0; $i < count($_POST['media_type']); $i++) {
            if (!empty($_POST['link'][$i])) {
                $groupLinksArr[] = [
                    'media' => $conn->real_escape_string($_POST['media_type'][$i]),
                    'town' => $conn->real_escape_string($_POST['town'][$i]),
                    'group_name' => $conn->real_escape_string($_POST['group_name'][$i]),
                    'url' => $conn->real_escape_string($_POST['link'][$i])
                ];
            }
        }
    }
    $group_links_json = json_encode($groupLinksArr);

    // Database Update / Insert
    if ($current_data) {
        $sql = "UPDATE about SET 
                bio='$bio', address='$address', contact_number='$contact_number', email='$email',
                desktop_banner='$desktop_banner', mobile_banner='$mobile_banner', profile_pic='$profile_pic',
                timetable='$timetable_json', group_links='$group_links_json'
                WHERE id=" . $current_data['id'];
    } else {
        $sql = "INSERT INTO about (bio, address, contact_number, email, desktop_banner, mobile_banner, profile_pic, timetable, group_links) 
                VALUES ('$bio', '$address', '$contact_number', '$email', '$desktop_banner', '$mobile_banner', '$profile_pic', '$timetable_json', '$group_links_json')";
    }

    if ($conn->query($sql)) {
        echo "<script>window.location.href='about_data.php?status=success';</script>";
        exit();
    }
}

// --- 2. Form එකට පෙන්වීමට දත්ත ලබා ගැනීම (Pre-fill Data) ---
$res = $conn->query("SELECT * FROM about LIMIT 1");
$data = $res->fetch_assoc();

$tt_data = json_decode($data['timetable'] ?? '[]', true);
if (!is_array($tt_data)) $tt_data = [];

$gl_data = json_decode($data['group_links'] ?? '[]', true);
if (!is_array($gl_data)) $gl_data = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher | About Data Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-50 pb-20">

<div class="max-w-4xl mx-auto p-6">
    <div class="flex items-center gap-4 mb-8">
        <a href="dashboard.php" class="bg-white p-3 rounded-2xl shadow-sm text-slate-600 hover:bg-slate-100"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-black text-slate-800 uppercase italic">Teacher Settings</h1>
    </div>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        
        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
            <h2 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-6"><i class="fas fa-images mr-2"></i> Banner & Profile Images</h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Desktop Banner (1920x700)</label>
                    <input type="file" name="desktop_banner" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"/>
                    <?php if(!empty($data['desktop_banner'])): ?>
                        <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-check-circle"></i> Image Uploaded</p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Mobile Banner (1600x500)</label>
                    <input type="file" name="mobile_banner" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"/>
                    <?php if(!empty($data['mobile_banner'])): ?>
                        <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-check-circle"></i> Image Uploaded</p>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Main Profile Image</label>
                    <input type="file" name="profile_pic" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"/>
                    <?php if(!empty($data['profile_pic'])): ?>
                        <p class="text-[10px] text-emerald-500 mt-1"><i class="fas fa-check-circle"></i> Image Uploaded</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
            <h2 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-6"><i class="fas fa-info-circle mr-2"></i> Basic Information</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-2">Teacher Biography</label>
                    <textarea name="bio" rows="4" class="w-full bg-slate-50 border-0 rounded-2xl p-4 text-sm focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($data['bio'] ?? '') ?></textarea>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <input type="text" name="contact_number" placeholder="Contact Number" value="<?= htmlspecialchars($data['contact_number'] ?? '') ?>" class="bg-slate-50 border-0 rounded-2xl p-4 text-sm w-full focus:ring-2 focus:ring-indigo-500">
                    <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="bg-slate-50 border-0 rounded-2xl p-4 text-sm w-full focus:ring-2 focus:ring-indigo-500">
                </div>
                <input type="text" name="address" placeholder="Teacher Address" value="<?= htmlspecialchars($data['address'] ?? '') ?>" class="bg-slate-50 border-0 rounded-2xl p-4 text-sm w-full focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
            <h2 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-6"><i class="fas fa-calendar-alt mr-2"></i> Class Timetable</h2>
            <div id="timetable-container" class="space-y-4">
                <?php if(count($tt_data) > 0): foreach($tt_data as $tt): ?>
                    <div class="flex items-center gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-100 tt-row">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 w-full">
                            <input type="text" name="year[]" value="<?= htmlspecialchars($tt['year']) ?>" placeholder="Year" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="stream[]" value="<?= htmlspecialchars($tt['stream']) ?>" placeholder="Stream" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="subject[]" value="<?= htmlspecialchars($tt['subject']) ?>" placeholder="Subject" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="day[]" value="<?= htmlspecialchars($tt['day']) ?>" placeholder="Day" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="time[]" value="<?= htmlspecialchars($tt['time']) ?>" placeholder="Time" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-times-circle"></i></button>
                    </div>
                <?php endforeach; else: ?>
                    <div class="flex items-center gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-100 tt-row">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 w-full">
                            <input type="text" name="year[]" placeholder="Year (2026)" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="stream[]" placeholder="Stream" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="subject[]" placeholder="Subject" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="day[]" placeholder="Day (Sunday)" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="time[]" placeholder="Time (8AM-12PM)" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-times-circle"></i></button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" onclick="addTimetableRow()" class="mt-4 text-[10px] font-bold text-indigo-600 uppercase tracking-widest"><i class="fas fa-plus mr-1"></i> Add Class Row</button>
        </div>

        <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-slate-100">
            <h2 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-6"><i class="fas fa-link mr-2"></i> Social & Group Links</h2>
            <div id="links-container" class="space-y-4">
                <?php if(count($gl_data) > 0): foreach($gl_data as $gl): ?>
                    <div class="flex items-center gap-2 link-row">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 w-full">
                            <select name="media_type[]" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                                <option value="whatsapp" <?= ($gl['media']=='whatsapp')?'selected':'' ?>>WhatsApp</option>
                                <option value="facebook" <?= ($gl['media']=='facebook')?'selected':'' ?>>Facebook</option>
                                <option value="youtube" <?= ($gl['media']=='youtube')?'selected':'' ?>>YouTube</option>
                                <option value="instagram" <?= ($gl['media']=='instagram')?'selected':'' ?>>Instagram</option>
                                <option value="tiktok" <?= ($gl['media']=='tiktok')?'selected':'' ?>>TikTok</option>
                            </select>
                            <input type="text" name="town[]" value="<?= htmlspecialchars($gl['town']) ?>" placeholder="Town" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="group_name[]" value="<?= htmlspecialchars($gl['group_name']) ?>" placeholder="Group Name" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="link[]" value="<?= htmlspecialchars($gl['url']) ?>" placeholder="Media Link" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-times-circle text-lg"></i></button>
                    </div>
                <?php endforeach; else: ?>
                    <div class="flex items-center gap-2 link-row">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 w-full">
                            <select name="media_type[]" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                                <option value="whatsapp">WhatsApp</option>
                                <option value="facebook">Facebook</option>
                                <option value="youtube">YouTube</option>
                                <option value="instagram">Instagram</option>
                                <option value="tiktok">TikTok</option>
                            </select>
                            <input type="text" name="town[]" placeholder="Town (e.g. Badulla)" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="group_name[]" placeholder="Group Name" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                            <input type="text" name="link[]" placeholder="Media Link" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-times-circle text-lg"></i></button>
                    </div>
                <?php endif; ?>
            </div>
            <button type="button" onclick="addLinkRow()" class="mt-4 text-[10px] font-bold text-emerald-600 uppercase tracking-widest">
                <i class="fas fa-plus-circle mr-1"></i> Add Another Link
            </button>
        </div>

        <button type="submit" class="w-full bg-slate-900 text-white p-5 rounded-[2rem] font-black uppercase text-xs tracking-[0.3em] shadow-xl hover:bg-indigo-600 transition-all">
            Save All Information
        </button>

    </form>
</div>

<script>
    function addTimetableRow() {
        const rowHTML = `
            <div class="flex items-center gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-100 tt-row mt-2">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 w-full">
                    <input type="text" name="year[]" placeholder="Year" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="stream[]" placeholder="Stream" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="subject[]" placeholder="Subject" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="day[]" placeholder="Day" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="time[]" placeholder="Time" class="p-2 text-xs bg-white rounded-xl border-0 focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-times-circle"></i></button>
            </div>
        `;
        document.getElementById('timetable-container').insertAdjacentHTML('beforeend', rowHTML);
    }

    function addLinkRow() {
        const rowHTML = `
            <div class="flex items-center gap-2 link-row mt-2">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 w-full">
                    <select name="media_type[]" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="facebook">Facebook</option>
                        <option value="youtube">YouTube</option>
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">TikTok</option>
                    </select>
                    <input type="text" name="town[]" placeholder="Town" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="group_name[]" placeholder="Group Name" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                    <input type="text" name="link[]" placeholder="Media Link" class="p-3 text-xs bg-slate-50 rounded-2xl border-0 focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 p-2"><i class="fas fa-times-circle text-lg"></i></button>
            </div>
        `;
        document.getElementById('links-container').insertAdjacentHTML('beforeend', rowHTML);
    }
</script>

<?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
<script>
    Swal.fire({
        title: 'Success!',
        text: 'Settings Updated Successfully.',
        icon: 'success',
        confirmButtonColor: '#4f46e5'
    });
</script>
<?php endif; ?>

</body>
</html>