<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$video_db_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : null;

if (!$video_db_id) {
    header("Location: st_recordings.php");
    exit();
}

// Expire date එකත් එක්කම Query එක ගමු
$query = "SELECT r.*, c.subject FROM recordings r 
          JOIN classes c ON r.class_id = c.class_id 
          JOIN student_classes sc ON c.class_id = sc.class_id 
          WHERE r.id = '$video_db_id' AND sc.student_id = '$student_id' 
          LIMIT 1";

$res = $conn->query($query);
$video = $res->fetch_assoc();

if (!$video) {
    exit("<div style='color:white; background:#000; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>Access Denied.</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Secure Stream - <?= $video['video_title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #000; color: white; margin: 0; 
            overflow: hidden; touch-action: manipulation;
            /* Disable Selection */
            -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;
        }

        .video-box { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 10; }
        .video-box iframe { width: 100vw; height: 140vh; transform: translateY(-15%); pointer-events: none; }

        /* Security Overlay - Protects Video from Direct Right Click/Inspect */
        .security-shield { position: absolute; inset: 0; z-index: 20; background: transparent; }

        .player-wrapper { position: relative; width: 100vw; height: 100vh; cursor: none; background: #000; overflow: hidden; }
        .player-wrapper.show-ui { cursor: default; }

        .ui-overlay { 
            position: absolute; inset: 0; z-index: 50; display: flex; flex-direction: column; justify-content: space-between; 
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 30%, transparent 70%, rgba(0,0,0,0.8) 100%);
            opacity: 0; pointer-events: none; transition: opacity 0.4s ease-in-out; 
        }
        .player-wrapper.show-ui .ui-overlay { opacity: 1; pointer-events: auto; }

        .prog-fill { height: 100%; width: 0%; background: #6366f1; border-radius: 10px; box-shadow: 0 0 15px #6366f1; position: relative; }
        .ctrl-btn { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(255,255,255,0.05); transition: 0.3s; }
        
        #speedMenu { display: none; position: absolute; bottom: 85px; right: 20px; background: rgba(15,23,42,0.98); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 10px; min-width: 140px; z-index: 70; }
        #speedMenu button { width: 100%; padding: 12px; text-align: left; font-size: 13px; font-weight: bold; border-radius: 10px; }

        /* Watermark - Moving text to discourage recording */
        .watermark {
            position: absolute; font-size: 12px; font-weight: 800; opacity: 0.15; color: white;
            z-index: 30; pointer-events: none; white-space: nowrap; text-transform: uppercase;
        }
    </style>
</head>
<body oncontextmenu="return false;"> <div class="player-wrapper show-ui" id="pWrapper" onclick="handleWrapperClick(event)">
        <div id="watermark" class="watermark"><?= $_SESSION['user_id'] ?> | PRO-STREAM PROTECTED</div>

        <div class="security-shield"></div>
        <div class="video-box"><div id="mainYT"></div></div>

        <div class="ui-overlay" id="ui">
            <div class="flex justify-between items-start pt-2">
                <div class="flex items-center gap-4">
                    <a href="st_recordings.php" class="ctrl-btn shrink-0"><i class="fas fa-arrow-left"></i></a>
                    <div class="overflow-hidden">
                        <h1 class="font-black italic uppercase tracking-tighter text-sm md:text-lg leading-tight line-clamp-1"><?= $video['video_title'] ?></h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[8px] font-black bg-rose-600 text-white px-2 py-0.5 rounded uppercase">Expires: <?= $video['expire_date'] ?></span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase italic tracking-widest"><?= $video['subject'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button class="ctrl-btn text-[10px] font-black" onclick="toggleSpeed(event)"><i class="fas fa-gauge-high"></i> <span id="speedTxt" class="ml-1">1.0x</span></button>
                    <button class="ctrl-btn" onclick="toggleFS(event)"><i class="fas fa-expand"></i></button>
                </div>
            </div>

            <div class="w-full max-w-6xl mx-auto">
                <div class="prog-area" id="progArea" style="width: 100%; height: 50px; display: flex; align-items: center; cursor: pointer;">
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.2); border-radius: 10px;">
                        <div id="progFill" class="prog-fill"></div>
                    </div>
                </div>
                <div class="flex justify-between items-center -mt-2">
                    <div class="flex items-center gap-6">
                        <button onclick="togglePlay(event)" class="w-10 h-10 flex items-center justify-center"><i class="fas fa-play text-xl" id="playBtn"></i></button>
                        <div class="text-[11px] font-bold tracking-widest"><span id="cur">00:00</span> <span class="text-white/30 mx-1">/</span> <span id="dur">00:00</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="speedMenu">
            <button onclick="setSpeed(0.5, event)">0.5x Slow</button>
            <button onclick="setSpeed(1.0, event)">1.0x Normal</button>
            <button onclick="setSpeed(1.5, event)">1.5x Fast</button>
            <button onclick="setSpeed(2.0, event)">2.0x Turbo</button>
        </div>
    </div>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
    // --- SECURITY: BLOCK KEYBOARD ---
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && (e.key === 'u' || e.key === 's' || e.key === 'p' || e.key === 'shift' || e.key === 'i' || e.key === 'j')) e.preventDefault();
        if (e.key === 'F12' || e.key === 'PrintScreen') e.preventDefault();
    });

    // --- MOVING WATERMARK ---
    const wm = document.getElementById('watermark');
    function moveWatermark() {
        const x = Math.random() * (window.innerWidth - 150);
        const y = Math.random() * (window.innerHeight - 50);
        wm.style.left = x + 'px';
        wm.style.top = y + 'px';
    }
    setInterval(moveWatermark, 5000);
    moveWatermark();

    let player, progInt, uiTimer, currentHistoryId = null;
    const wrapper = document.getElementById('pWrapper');
    const speedMenu = document.getElementById('speedMenu');
    
    // --- YOUTUBE API READY ---
    function onYouTubeIframeAPIReady() {
        player = new YT.Player('mainYT', {
            videoId: '<?= $video['video_id'] ?>',
            playerVars: { 'controls': 0, 'disablekb': 1, 'rel': 0, 'modestbranding': 1, 'playsinline': 1, 'autoplay': 1 },
            events: { 'onReady': onReady, 'onStateChange': onStateChange }
        });
    }

    function onReady() {
        document.getElementById('dur').innerText = format(player.getDuration());
        wakeUpUI();
    }

    function wakeUpUI() {
        wrapper.classList.add('show-ui');
        clearTimeout(uiTimer);
        uiTimer = setTimeout(() => {
            if (player && player.getPlayerState() === YT.PlayerState.PLAYING) {
                wrapper.classList.remove('show-ui');
                speedMenu.style.display = 'none';
            }
        }, 3000);
    }

    ['mousemove', 'touchstart'].forEach(evt => wrapper.addEventListener(evt, wakeUpUI));

    // --- TRACKING LOGIC ---
    function saveHistory(action) {
        const url = 'save_history.php';
        
        if (action === 'start') {
            const formData = new URLSearchParams();
            formData.append('action', 'start');
            formData.append('recording_id', '<?= $video_db_id ?>');

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(res => res.text())
            .then(id => { currentHistoryId = id; });
        } 
        else if (action === 'stop' && currentHistoryId) {
            // පේජ් එක වහන වෙලාවටත් වැඩ කරන විදිහට sendBeacon පාවිච්චි කිරීම
            const stopData = new FormData();
            stopData.append('action', 'stop');
            stopData.append('history_id', currentHistoryId);
            
            if (navigator.sendBeacon) {
                navigator.sendBeacon(url, stopData);
            } else {
                fetch(url, { method: 'POST', body: stopData, keepalive: true });
            }
        }
    }

    function onStateChange(e) {
        const btn = document.getElementById('playBtn');
        if (e.data == YT.PlayerState.PLAYING) {
            btn.className = 'fas fa-pause text-xl';
            startProg();
            if (!currentHistoryId) saveHistory('start');
        } else {
            btn.className = 'fas fa-play text-xl';
            clearInterval(progInt);
            saveHistory('stop');
        }
    }

    // --- BUG FIX: පේජ් එක වහන විට දත්ත යැවීම ---
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') {
            saveHistory('stop');
        }
    });

    window.addEventListener('pagehide', function() {
        saveHistory('stop');
    });

    // --- UI CONTROLS ---
    function handleWrapperClick(e) {
        if (e.target.closest('button') || e.target.closest('.prog-area') || e.target.closest('a') || e.target.closest('#speedMenu')) return;
        if (!wrapper.classList.contains('show-ui')) { wakeUpUI(); return; }
        togglePlay(e);
    }

    function togglePlay(e) { 
        if(e) e.stopPropagation();
        const s = player.getPlayerState(); 
        if (s == 1) player.pauseVideo(); else player.playVideo();
    }

    function toggleSpeed(e) { 
        if(e) e.stopPropagation();
        speedMenu.style.display = speedMenu.style.display === 'block' ? 'none' : 'block'; 
    }

    function setSpeed(s, e) { 
        if(e) e.stopPropagation();
        player.setPlaybackRate(s); document.getElementById('speedTxt').innerText = s + 'x'; speedMenu.style.display = 'none';
    }

    function startProg() {
        progInt = setInterval(() => {
            const c = player.getCurrentTime(), d = player.getDuration();
            document.getElementById('progFill').style.width = (c/d*100) + '%';
            document.getElementById('cur').innerText = format(c);
        }, 1000);
    }

    function format(t) { const m = Math.floor(t/60), s = Math.floor(t%60); return (m<10?'0':'')+m+':'+(s<10?'0':'')+s; }
    
    function toggleFS(e) { 
        if(e) e.stopPropagation();
        if (!document.fullscreenElement) wrapper.requestFullscreen(); else document.exitFullscreen(); 
    }

    document.getElementById('progArea').addEventListener('click', (e) => {
        const pct = e.offsetX / e.target.offsetWidth; 
        player.seekTo(pct * player.getDuration());
    });
</script>
</body>
</html>