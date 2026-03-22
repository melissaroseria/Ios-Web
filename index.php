<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>iPass Lock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        :root {
            --blur: blur(40px) saturate(180%);
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: -apple-system, 'SF Pro Display', 'Helvetica Neue', sans-serif;
        }

        /* ── WALLPAPER ── */
        .wallpaper {
            position: fixed; inset: 0;
            background: 
                radial-gradient(ellipse at 20% 20%, #1a1a4e 0%, transparent 60%),
                radial-gradient(ellipse at 80% 80%, #0d2a1a 0%, transparent 60%),
                radial-gradient(ellipse at 60% 30%, #2d1b4e 0%, transparent 50%),
                linear-gradient(160deg, #0a0a1a 0%, #0f1f2e 50%, #0a1a0f 100%);
            z-index: 0;
        }
        .wallpaper::after {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='400'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='400' height='400' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.5;
        }

        /* ── LOCK SCREEN ── */
        #lockScreen {
            position: fixed; inset: 0;
            display: flex; flex-direction: column;
            align-items: center;
            z-index: 10;
            padding: env(safe-area-inset-top, 50px) 20px env(safe-area-inset-bottom, 20px);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease;
        }

        /* TIME */
        .time-block {
            margin-top: 40px;
            text-align: center;
            color: white;
            animation: fadeDown 0.8s ease both;
        }
        .time-block .time {
            font-size: 82px;
            font-weight: 200;
            letter-spacing: -2px;
            line-height: 1;
            text-shadow: 0 2px 20px rgba(0,0,0,0.4);
        }
        .time-block .date {
            font-size: 19px;
            font-weight: 400;
            opacity: 0.85;
            margin-top: 6px;
            letter-spacing: 0.2px;
        }

        /* NOTIFICATION */
        .notif-area {
            width: 100%;
            max-width: 390px;
            margin-top: 36px;
            animation: fadeUp 0.9s 0.3s ease both;
        }

        .notif-card {
            background: rgba(255,255,255,0.18);
            backdrop-filter: var(--blur);
            -webkit-backdrop-filter: var(--blur);
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.22);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.2);
            position: relative;
            overflow: hidden;
        }
        .notif-card::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 60%);
            pointer-events: none;
        }
        .notif-card:active { transform: scale(0.97); background: rgba(255,255,255,0.24); }

        .notif-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1c1c1e, #2c2c2e);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.1);
            font-size: 20px;
        }

        .notif-text {
            flex: 1;
            min-width: 0;
        }
        .notif-app {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .notif-title {
            font-size: 15px;
            font-weight: 600;
            color: white;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .notif-body {
            font-size: 13px;
            color: rgba(255,255,255,0.75);
            margin-top: 1px;
        }
        .notif-time {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            align-self: flex-start;
            flex-shrink: 0;
        }

        /* DEMO BADGE */
        .demo-badge {
            margin-top: 10px;
            display: flex;
            justify-content: center;
        }
        .demo-badge span {
            background: rgba(255,165,0,0.22);
            border: 1px solid rgba(255,165,0,0.4);
            color: #ffb347;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            backdrop-filter: blur(10px);
        }

        /* BOTTOM */
        .lock-bottom {
            position: fixed; bottom: 40px; left: 0; right: 0;
            display: flex; justify-content: space-between;
            padding: 0 40px;
            animation: fadeUp 0.9s 0.5s ease both;
        }
        .lock-bottom-btn {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            color: rgba(255,255,255,0.8);
            font-size: 11px;
            cursor: pointer;
        }
        .lock-bottom-btn .icon-circle {
            width: 50px; height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }

        /* ── PIN SCREEN ── */
        #pinScreen {
            position: fixed; inset: 0;
            display: flex; flex-direction: column;
            align-items: center;
            justify-content: space-between;
            z-index: 20;
            padding: 60px 20px 40px;
            background: rgba(0,0,0,0.55);
            backdrop-filter: blur(60px) saturate(200%);
            -webkit-backdrop-filter: blur(60px) saturate(200%);
            transform: translateY(100%);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #pinScreen.open { transform: translateY(0); }

        .pin-header { text-align: center; color: white; }
        .pin-header .pin-lock { font-size: 28px; margin-bottom: 12px; opacity: 0.9; }
        .pin-header h2 {
            font-size: 22px; font-weight: 600; letter-spacing: -0.3px;
            margin-bottom: 4px;
        }
        .pin-header p { font-size: 14px; color: rgba(255,255,255,0.6); }

        /* DOTS */
        .pin-dots {
            display: flex; gap: 20px;
            margin: 10px 0;
        }
        .pin-dot {
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.7);
            transition: all 0.15s ease;
        }
        .pin-dot.filled {
            background: white;
            border-color: white;
            transform: scale(1.1);
        }
        .pin-dot.error {
            border-color: #ff3b30;
            background: #ff3b30;
            animation: shake 0.4s ease;
        }

        /* KEYPAD */
        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            width: 100%;
            max-width: 320px;
        }
        .key {
            aspect-ratio: 1;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.12);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            cursor: pointer;
            transition: background 0.1s, transform 0.1s;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .key:active { background: rgba(255,255,255,0.35); transform: scale(0.93); }
        .key .k-num { font-size: 28px; font-weight: 300; line-height: 1; }
        .key .k-sub { font-size: 9px; font-weight: 600; letter-spacing: 2px; opacity: 0.6; margin-top: 2px; }
        .key-empty { background: transparent !important; border: none !important; box-shadow: none !important; }
        .key-del { background: rgba(255,255,255,0.08); }
        .key-del i { font-size: 22px; }

        /* CANCEL */
        .pin-cancel {
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            cursor: pointer;
            padding: 8px 20px;
            border-radius: 20px;
            transition: color 0.2s;
        }
        .pin-cancel:active { color: white; }

        .error-msg {
            color: #ff3b30;
            font-size: 14px;
            font-weight: 500;
            height: 20px;
            text-align: center;
            transition: opacity 0.3s;
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
        }
        @keyframes notifPulse {
            0%   { box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 0 rgba(255,183,71,0.4); }
            70%  { box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 12px rgba(255,183,71,0); }
            100% { box-shadow: 0 8px 32px rgba(0,0,0,0.3), 0 0 0 0 rgba(255,183,71,0); }
        }
        .notif-pulse { animation: notifPulse 2s 1.5s ease infinite; }
    </style>
</head>
<body>
<div class="wallpaper"></div>

<!-- ═══ LOCK SCREEN ═══ -->
<div id="lockScreen">
    <div class="time-block">
        <div class="time" id="clock">--:--</div>
        <div class="date" id="dateStr">--</div>
    </div>

    <div class="notif-area">
        <!-- Notification Card -->
        <div class="notif-card notif-pulse" onclick="openPin()">
            <div class="notif-icon">🔐</div>
            <div class="notif-text">
                <div class="notif-app">iPass</div>
                <div class="notif-title">Cihaz Demo Modunda Aktif</div>
                <div class="notif-body">Devam etmek için dokunun →</div>
            </div>
            <div class="notif-time" id="notifTime">şimdi</div>
        </div>

        <div class="demo-badge">
            <span>⚠ DEMO MODU · iPass 2005</span>
        </div>
    </div>

    <div class="lock-bottom">
        <div class="lock-bottom-btn">
            <div class="icon-circle">🔦</div>
            <span>El Feneri</span>
        </div>
        <div class="lock-bottom-btn">
            <div class="icon-circle">📷</div>
            <span>Kamera</span>
        </div>
    </div>
</div>

<!-- ═══ PIN SCREEN ═══ -->
<div id="pinScreen">
    <div class="pin-header">
        <div class="pin-lock"><i class="fas fa-lock"></i></div>
        <h2>iPass Kilidini Aç</h2>
        <p>4 haneli parolayı girin</p>
    </div>

    <div class="pin-dots" id="pinDots">
        <div class="pin-dot" id="d0"></div>
        <div class="pin-dot" id="d1"></div>
        <div class="pin-dot" id="d2"></div>
        <div class="pin-dot" id="d3"></div>
    </div>

    <div class="error-msg" id="errorMsg"></div>

    <div class="keypad">
        <div class="key" onclick="pressKey('1')"><span class="k-num">1</span><span class="k-sub">&nbsp;</span></div>
        <div class="key" onclick="pressKey('2')"><span class="k-num">2</span><span class="k-sub">ABC</span></div>
        <div class="key" onclick="pressKey('3')"><span class="k-num">3</span><span class="k-sub">DEF</span></div>
        <div class="key" onclick="pressKey('4')"><span class="k-num">4</span><span class="k-sub">GHI</span></div>
        <div class="key" onclick="pressKey('5')"><span class="k-num">5</span><span class="k-sub">JKL</span></div>
        <div class="key" onclick="pressKey('6')"><span class="k-num">6</span><span class="k-sub">MNO</span></div>
        <div class="key" onclick="pressKey('7')"><span class="k-num">7</span><span class="k-sub">PQRS</span></div>
        <div class="key" onclick="pressKey('8')"><span class="k-num">8</span><span class="k-sub">TUV</span></div>
        <div class="key" onclick="pressKey('9')"><span class="k-num">9</span><span class="k-sub">WXYZ</span></div>
        <div class="key key-del" onclick="deleteKey()"><i class="fas fa-delete-left"></i></div>
        <div class="key" onclick="pressKey('0')"><span class="k-num">0</span><span class="k-sub">&nbsp;</span></div>
        <div class="key key-empty"></div>
    </div>

    <div class="pin-cancel" onclick="closePin()">İptal</div>
</div>

<script>
    // ── Clock ──
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2,'0');
        const m = String(now.getMinutes()).padStart(2,'0');
        document.getElementById('clock').textContent = h + ':' + m;
        const days = ['Pazar','Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi'];
        const months = ['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'];
        document.getElementById('dateStr').textContent =
            days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()];
    }
    updateClock(); setInterval(updateClock, 1000);

    // ── PIN logic ──
    let input = '';
    const dots = [0,1,2,3].map(i => document.getElementById('d'+i));

    function openPin() {
        document.getElementById('pinScreen').classList.add('open');
        input = ''; updateDots(); clearError();
    }
    function closePin() {
        document.getElementById('pinScreen').classList.remove('open');
        input = ''; updateDots(); clearError();
    }

    function pressKey(n) {
        if (input.length >= 4) return;
        input += n;
        updateDots();
        if (input.length === 4) setTimeout(checkPin, 320);
    }
    function deleteKey() {
        if (!input.length) return;
        input = input.slice(0,-1);
        updateDots(); clearError();
    }
    function updateDots() {
        dots.forEach((d,i) => {
            d.classList.remove('filled','error');
            if (i < input.length) d.classList.add('filled');
        });
    }
    function clearError() { document.getElementById('errorMsg').textContent = ''; }

    function checkPin() {
        fetch("src/get_password.php")
            .then(r => r.text())
            .then(stored => {
                if (input === stored.trim()) {
                    // Success — slide screen up then redirect
                    document.getElementById('pinScreen').style.transform = 'translateY(-100%)';
                    document.getElementById('lockScreen').style.transform = 'translateY(-60px)';
                    document.getElementById('lockScreen').style.opacity = '0';
                    setTimeout(() => { window.location.href = "app/main.php"; }, 500);
                } else {
                    dots.forEach(d => { d.classList.remove('filled'); d.classList.add('error'); });
                    document.getElementById('errorMsg').textContent = 'Yanlış Parola';
                    navigator.vibrate && navigator.vibrate([50,30,50]);
                    setTimeout(() => {
                        input = ''; updateDots(); clearError();
                    }, 800);
                }
            })
            .catch(() => {
                document.getElementById('errorMsg').textContent = 'Bağlantı hatası';
                setTimeout(() => { input = ''; updateDots(); clearError(); }, 1000);
            });
    }

    // Swipe up to open pin
    let touchY = 0;
    document.getElementById('lockScreen').addEventListener('touchstart', e => touchY = e.touches[0].clientY);
    document.getElementById('lockScreen').addEventListener('touchend', e => {
        if (touchY - e.changedTouches[0].clientY > 60) openPin();
    });
</script>
</body>
</html>