<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$class_id = isset($_GET['class_id']) ? mysqli_real_escape_string($conn, $_GET['class_id']) : null;
$video = $conn->query("SELECT * FROM recordings WHERE class_id = '$class_id' AND (status = 'released' OR (status = 'scheduled' AND expire_date > NOW())) ORDER BY added_date DESC LIMIT 1")->fetch_assoc();
if (!$video) { exit("No videos found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pro Stream - <?= $video['video_title'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #000; color: white; margin: 0; overflow: hidden; touch-action: manipulation; }

        .video-box { position: absolute; inset: 0; width: 100%; height: 100%; }
        /* Scale up to hide YT branding/controls entirely */
        .video-box iframe { width: 100vw; height: 140vh; transform: translateY(-15%); pointer-events: none; }

        /* Wrapper cursor logic */
        .player-wrapper { position: relative; width: 100vw; height: 100vh; cursor: none; background: #000; }
        .player-wrapper.show-ui { cursor: default; }

        /* UI Overlay with Auto-Hide Logic */
        .ui-overlay { 
            position: absolute; inset: 0; z-index: 50; display: flex; flex-direction: column; justify-content: space-between; 
            padding: 20px; md:padding: 30px; 
            background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 30%, transparent 70%, rgba(0,0,0,0.8) 100%);
            opacity: 0; pointer-events: none; transition: opacity 0.5s ease-in-out; 
        }
        /* Only show UI when class is active */
        .player-wrapper.show-ui .ui-overlay { opacity: 1; pointer-events: auto; }

        /* Mobile optimized Progress Bar */
        .prog-area { width: 100%; height: 50px; display: flex; align-items: center; cursor: pointer; position: relative; }
        .prog-bg { width: 100%; height: 6px; background: rgba(255,255,255,0.2); border-radius: 10px; position: relative; transition: height 0.2s; }
        .prog-area:active .prog-bg { height: 10px; }
        .prog-fill { height: 100%; width: 0%; background: #6366f1; border-radius: 10px; box-shadow: 0 0 15px #6366f1; position: relative; }
        .prog-handle { position: absolute; right: -8px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; background: white; border-radius: 50%; opacity: 0; transition: 0.2s; box-shadow: 0 0 10px rgba(0,0,0,0.5); }
        .player-wrapper.show-ui .prog-handle { opacity: 1; }

        .ctrl-btn { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(255,255,255,0.05); transition: 0.3s; }
        .ctrl-btn:active, .ctrl-btn:hover { background: rgba(255,255,255,0.2); color: #6366f1; transform: scale(0.95); }
        
        #speedMenu { display: none; position: absolute; bottom: 80px; right: 20px; background: rgba(15,23,42,0.95); backdrop-filter: blur(10px); 
            border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 10px; min-width: 140px; z-index: 70; }
        #speedMenu button { width: 100%; padding: 12px; text-align: left; font-size: 13px; font-weight: bold; border-radius: 10px; transition: 0.2s; }
        #speedMenu button:active { background: #6366f1; }

        .seek-ripple { position: absolute; top: 50%; width: 120px; height: 120px; background: rgba(255,255,255,0.1); 
            border-radius: 50%; transform: translate(-50%, -50%); opacity: 0; pointer-events: none; z-index: 60;
            display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .seek-ripple.active { animation: ripple 0.5s ease-out; }
        @keyframes ripple { 0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); } 50% { opacity: 1; } 100% { opacity: 0; transform: translate(-50%, -50%) scale(1.5); } }

        /* Network Toast */
        .network-toast { position: fixed; top: 20px; left: 50%; transform: translate(-50%, -20px); background: rgba(225, 29, 72, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); color: white; padding: 10px 20px; border-radius: 50px; display: flex; align-items: center; gap: 10px; font-size: 11px; font-weight: 800; text-transform: uppercase; opacity: 0; pointer-events: none; transition: 0.4s; z-index: 100; }
        .network-toast.active { opacity: 1; transform: translate(-50%, 0); }
    </style>
</head>
<body>

    <div id="netWarning" class="network-toast">
        <i class="fas fa-spinner fa-spin"></i><span>Connection Slow...</span>
    </div>

    <div id="rippleLeft" class="seek-ripple" style="left: 20%;"><i class="fas fa-backward text-3xl mb-1"></i><span class="text-[12px] font-bold">10s</span></div>
    <div id="rippleRight" class="seek-ripple" style="left: 80%;"><i class="fas fa-forward text-3xl mb-1"></i><span class="text-[12px] font-bold">10s</span></div>

    <div class="player-wrapper show-ui" id="pWrapper" onclick="handleWrapperClick(event)">
        <div class="video-box"><div id="mainYT"></div></div>

        <div class="ui-overlay" id="ui">
            <div class="flex justify-between items-start pt-2">
                <div class="flex items-center gap-3 md:gap-4">
                    <a href="teachers_recordings.php" class="ctrl-btn shrink-0"><i class="fas fa-arrow-left"></i></a>
                    <h1 class="font-black italic uppercase tracking-tighter text-lg md:text-xl line-clamp-1"><?= $video['video_title'] ?></h1>
                </div>
                <div class="flex gap-2 md:gap-3 shrink-0">
                    <button class="ctrl-btn text-[11px] font-black" onclick="toggleSpeed(event)"><i class="fas fa-gauge-high md:mr-2"></i> <span id="speedTxt" class="hidden md:inline">1.0x</span></button>
                    <button class="ctrl-btn" onclick="toggleFS(event)"><i class="fas fa-expand"></i></button>
                </div>
            </div>

            <div class="flex-1 flex items-center justify-center pointer-events-none">
                <div id="centerFeedback" class="w-20 h-20 bg-black/50 rounded-full flex items-center justify-center backdrop-blur-md opacity-0 transition-opacity transform scale-50">
                    <i class="fas fa-play text-3xl text-white" id="centerIcon"></i>
                </div>
            </div>

            <div class="w-full max-w-6xl mx-auto pb-4 md:pb-0">
                <div class="prog-area" id="progArea">
                    <div class="prog-bg">
                        <div class="prog-fill" id="progFill"><div class="prog-handle"></div></div>
                    </div>
                </div>
                <div class="flex justify-between items-center mt-1 px-1">
                    <div class="flex items-center gap-4 md:gap-6">
                        <button onclick="togglePlay(event)" class="w-10 h-10 flex items-center justify-center"><i class="fas fa-play text-xl md:text-2xl" id="playBtn"></i></button>
                        <div class="text-[11px] md:text-xs font-bold tracking-widest"><span id="cur">00:00</span> <span class="text-white/30 mx-1">/</span> <span id="dur">00:00</span></div>
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
        let player, progInt, clickTimer, bufferTimer, uiTimer;
        const wrapper = document.getElementById('pWrapper');
        const speedMenu = document.getElementById('speedMenu');
        const netWarn = document.getElementById('netWarning');
        const centerFeedback = document.getElementById('centerFeedback');
        const centerIcon = document.getElementById('centerIcon');
        
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('mainYT', {
                videoId: '<?= $video['video_id'] ?>',
                playerVars: { 'controls': 0, 'disablekb': 1, 'rel': 0, 'modestbranding': 1, 'iv_load_policy': 3, 'playsinline': 1 },
                events: { 'onReady': onReady, 'onStateChange': onStateChange }
            });
        }

        function onReady() {
            document.getElementById('dur').innerText = format(player.getDuration());
            wakeUpUI(); // Wake up UI initially
        }

        // --- SMART UI AUTO-HIDE LOGIC ---
        function wakeUpUI() {
            wrapper.classList.add('show-ui');
            clearTimeout(uiTimer);
            // Hide after 3 seconds ONLY IF playing
            uiTimer = setTimeout(() => {
                if (player && player.getPlayerState() === YT.PlayerState.PLAYING) {
                    wrapper.classList.remove('show-ui');
                    speedMenu.style.display = 'none'; // Also hide menu
                }
            }, 3000);
        }

        // Wake up UI on ANY mouse movement or touch
        ['mousemove', 'touchstart', 'touchmove'].forEach(evt => {
            wrapper.addEventListener(evt, wakeUpUI, { passive: true });
        });

        // --- CORE PLAYER STATE ---
        function onStateChange(e) {
            const btn = document.getElementById('playBtn');
            clearTimeout(bufferTimer); netWarn.classList.remove('active');

            if (e.data == YT.PlayerState.PLAYING) {
                btn.className = 'fas fa-pause text-xl md:text-2xl';
                startProg(); wakeUpUI(); // Start auto-hide timer
            } 
            else if (e.data == YT.PlayerState.BUFFERING) {
                bufferTimer = setTimeout(() => { netWarn.classList.add('active'); }, 2000);
                wakeUpUI();
            }
            else { 
                btn.className = 'fas fa-play text-xl md:text-2xl';
                clearInterval(progInt);
                // If paused, keep UI visible permanently
                wrapper.classList.add('show-ui'); 
                clearTimeout(uiTimer);
            }
        }

        // --- CLICK & DOUBLE TAP LOGIC (Mobile Safe) ---
        function handleWrapperClick(e) {
            // Prevent interference if clicking buttons or progress bar
            if (e.target.closest('button') || e.target.closest('.prog-area') || e.target.closest('a') || e.target.closest('#speedMenu')) {
                wakeUpUI(); return; 
            }

            // If UI is currently hidden, First tap ONLY wakes it up.
            if (!wrapper.classList.contains('show-ui')) {
                wakeUpUI();
                return;
            }

            wakeUpUI(); // Ensure it stays awake

            if (clickTimer) { 
                clearTimeout(clickTimer); clickTimer = null; 
                // Double Click Detected
                const rect = wrapper.getBoundingClientRect();
                if ((e.clientX - rect.left) < rect.width / 2) seek(-10, 'rippleLeft'); 
                else seek(10, 'rippleRight');
            } else { 
                // Single Click Detected
                clickTimer = setTimeout(() => { 
                    togglePlay(e); 
                    clickTimer = null; 
                }, 300); 
            }
        }

        function seek(sec, rippleId) {
            player.seekTo(player.getCurrentTime() + sec);
            const r = document.getElementById(rippleId);
            r.classList.add('active'); setTimeout(() => r.classList.remove('active'), 500);
            showCenterFeedback(sec > 0 ? 'fa-forward' : 'fa-backward');
        }

        function togglePlay(e) { 
            if(e) e.stopPropagation(); // Prevent wrapper click event
            const s = player.getPlayerState(); 
            if (s == 1) { player.pauseVideo(); showCenterFeedback('fa-pause'); } 
            else { player.playVideo(); showCenterFeedback('fa-play'); }
        }

        function showCenterFeedback(iconClass) {
            centerIcon.className = `fas ${iconClass} text-4xl text-white`;
            centerFeedback.classList.remove('opacity-0', 'scale-50');
            centerFeedback.classList.add('opacity-100', 'scale-100');
            setTimeout(() => {
                centerFeedback.classList.remove('opacity-100', 'scale-100');
                centerFeedback.classList.add('opacity-0', 'scale-50');
            }, 500);
        }

        // --- MENUS & PROGRESS ---
        function toggleSpeed(e) { 
            if(e) e.stopPropagation();
            speedMenu.style.display = speedMenu.style.display === 'block' ? 'none' : 'block'; 
            wakeUpUI();
        }
        function setSpeed(s, e) { 
            if(e) e.stopPropagation();
            player.setPlaybackRate(s); document.getElementById('speedTxt').innerText = s + 'x'; speedMenu.style.display = 'none'; wakeUpUI();
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
            if (!document.fullscreenElement) wrapper.requestFullscreen().catch(err => console.log(err)); 
            else document.exitFullscreen(); 
        }

        // Seek on progress bar click/touch
        document.getElementById('progArea').addEventListener('click', (e) => {
            e.stopPropagation();
            const pct = e.offsetX / e.target.offsetWidth; 
            player.seekTo(pct * player.getDuration());
            wakeUpUI();
        });
    </script>
</body>
</html>