<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Bu iPhone Hakkında</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

:root {
  --bg: #1C1C1E;
  --bg2: #2C2C2E;
  --bg3: #3A3A3C;
  --blue: #007AFF;
  --cyan: #00BCD4;
  --text: #FFFFFF;
  --text2: #8E8E93;
  --border: rgba(255,255,255,0.08);
  --blur: blur(40px) saturate(180%);
  --green: #30D158;
  --yellow: #FFD60A;
}

html, body {
  min-height: 100%; background: var(--bg);
  font-family: -apple-system, 'SF Pro Display', 'Helvetica Neue', sans-serif;
  color: var(--text);
}

/* ── NAV BAR ── */
.nav-bar {
  position: sticky; top: 0; z-index: 50;
  background: rgba(28,28,30,0.92);
  backdrop-filter: var(--blur); -webkit-backdrop-filter: var(--blur);
  border-bottom: 0.5px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 20px;
  padding-top: max(14px, env(safe-area-inset-top));
}
.nav-back {
  color: var(--blue); font-size: 17px;
  display: flex; align-items: center; gap: 4px;
  cursor: pointer; text-decoration: none;
}
.nav-title {
  font-size: 17px; font-weight: 600;
  position: absolute; left: 50%; transform: translateX(-50%);
}
.nav-right { width: 60px; }

/* ── DEVICE HERO ── */
.device-hero {
  display: flex; flex-direction: column; align-items: center;
  padding: 32px 20px 24px; gap: 12px;
  background: var(--bg);
}
.device-img-wrap {
  width: 80px; height: 80px;
  display: flex; align-items: center; justify-content: center;
  position: relative;
}
.device-img-wrap svg { width: 60px; height: 60px; filter: drop-shadow(0 4px 16px rgba(0,0,0,0.5)); }
.device-name { font-size: 22px; font-weight: 700; letter-spacing: -0.4px; }
.device-model { font-size: 14px; color: var(--text2); margin-top: 2px; }

/* ── VERSION BADGE ── */
.version-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(0,188,212,0.12);
  border: 0.5px solid rgba(0,188,212,0.35);
  border-radius: 20px; padding: 5px 14px;
  font-size: 13px; font-weight: 600; color: var(--cyan);
  margin-top: 4px;
}
.version-badge i { font-size: 11px; }

/* ── SECTION ── */
.section { margin: 0 0 8px; }
.section-label {
  font-size: 13px; color: var(--text2);
  padding: 16px 20px 8px;
  text-transform: uppercase; letter-spacing: 0.4px; font-weight: 500;
}

/* ── LIST GROUP ── */
.list-group {
  background: var(--bg2);
  border-radius: 12px; overflow: hidden;
  margin: 0 16px;
  border: 0.5px solid var(--border);
}
.list-row {
  display: flex; align-items: center;
  padding: 12px 16px;
  border-bottom: 0.5px solid var(--border);
  gap: 12px; min-height: 48px;
}
.list-row:last-child { border-bottom: none; }
.list-row.tappable { cursor: pointer; }
.list-row.tappable:active { background: rgba(255,255,255,0.05); }

.row-icon-wrap {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; font-size: 15px;
}
.row-label { flex: 1; font-size: 15px; }
.row-value {
  font-size: 15px; color: var(--text2);
  text-align: right; max-width: 55%;
  word-break: break-all;
}
.row-value.mono { font-family: 'SF Mono', 'Courier New', monospace; font-size: 13px; }
.row-value.green { color: var(--green); }
.row-value.yellow { color: var(--yellow); }
.row-value.cyan { color: var(--cyan); }
.row-chevron { color: var(--text2); font-size: 13px; margin-left: 6px; opacity: 0.6; }

/* Storage bar */
.storage-section { margin: 0 16px 8px; }
.storage-bar-wrap {
  background: var(--bg2); border-radius: 12px; padding: 16px;
  border: 0.5px solid var(--border);
}
.storage-bar-label {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 10px;
}
.storage-bar-label span { font-size: 13px; color: var(--text2); }
.storage-bar-label strong { font-size: 15px; }
.storage-track {
  height: 8px; background: rgba(255,255,255,0.1);
  border-radius: 4px; overflow: hidden;
  display: flex; gap: 1px;
}
.storage-seg {
  height: 100%; border-radius: 2px;
  transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
}
.seg-os   { background: #007AFF; width: 12%; }
.seg-apps { background: #30D158; width: 28%; }
.seg-data { background: #FFD60A; width: 18%; }
.seg-media{ background: #FF9F0A; width: 15%; }
.seg-free { background: rgba(255,255,255,0.15); flex:1; }
.storage-legend {
  display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px;
}
.legend-item { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--text2); }
.legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }

/* FAQ accordion */
.faq-item {
  background: var(--bg2); border-radius: 12px;
  margin: 0 16px 8px; overflow: hidden;
  border: 0.5px solid var(--border);
}
.faq-question {
  width: 100%; background: transparent; border: none;
  color: var(--text); font-size: 15px; font-weight: 500;
  text-align: left; padding: 14px 16px;
  display: flex; justify-content: space-between; align-items: center;
  cursor: pointer; font-family: -apple-system, sans-serif;
  gap: 10px;
}
.faq-question:active { background: rgba(255,255,255,0.04); }
.faq-chevron { color: var(--text2); font-size: 12px; transition: transform 0.25s ease; flex-shrink: 0; }
.faq-answer {
  max-height: 0; overflow: hidden;
  transition: max-height 0.35s cubic-bezier(0.4,0,0.2,1), padding 0.25s ease;
  background: rgba(0,0,0,0.2); border-top: 0px solid var(--border);
  font-size: 14px; color: rgba(255,255,255,0.75); line-height: 1.6;
  padding: 0 16px;
}
.faq-item.open .faq-answer {
  max-height: 300px; padding: 14px 16px;
  border-top: 0.5px solid var(--border);
}
.faq-item.open .faq-chevron { transform: rotate(90deg); }

/* Button group */
.btn-group { display: flex; gap: 10px; }
.link-btn {
  flex: 1; text-align: center;
  background: rgba(0,122,255,0.15);
  border: 0.5px solid rgba(0,122,255,0.4);
  color: var(--blue); font-size: 14px; font-weight: 600;
  padding: 10px 6px; border-radius: 10px;
  text-decoration: none; transition: opacity 0.2s;
}
.link-btn:active { opacity: 0.6; }

/* Bottom spacer */
.bottom-pad { height: 40px; }

/* Animate in */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
.list-group, .faq-item, .storage-bar-wrap, .device-hero {
  animation: fadeUp 0.4s ease both;
}
</style>
</head>
<body>

<!-- NAV -->
<div class="nav-bar">
  <a class="nav-back" href="../config.php">
    <i class="fas fa-chevron-left"></i> Ayarlar
  </a>
  <span class="nav-title">Hakkında</span>
  <div class="nav-right"></div>
</div>

<!-- DEVICE HERO -->
<div class="device-hero">
  <div class="device-img-wrap">
    <!-- iPhone 6S SVG -->
    <svg viewBox="0 0 100 180" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="8" y="2" width="84" height="176" rx="16" fill="#2C2C2E" stroke="#48484A" stroke-width="1.5"/>
      <rect x="13" y="8" width="74" height="164" rx="12" fill="#1C1C1E"/>
      <!-- screen -->
      <rect x="16" y="22" width="68" height="120" rx="4" fill="#0A0A0A"/>
      <!-- iOS glow on screen -->
      <rect x="16" y="22" width="68" height="120" rx="4" fill="url(#screenGrad)" opacity="0.6"/>
      <!-- home button -->
      <circle cx="50" cy="158" r="9" fill="#2C2C2E" stroke="#48484A" stroke-width="1"/>
      <circle cx="50" cy="158" r="6" fill="#3A3A3C"/>
      <!-- front cam -->
      <circle cx="50" cy="14" r="2.5" fill="#3A3A3C"/>
      <!-- speaker grill -->
      <rect x="38" y="12.5" width="24" height="3" rx="1.5" fill="#3A3A3C"/>
      <!-- side buttons -->
      <rect x="6" y="42" width="3" height="18" rx="1.5" fill="#48484A"/>
      <rect x="6" y="64" width="3" height="14" rx="1.5" fill="#48484A"/>
      <rect x="6" y="82" width="3" height="14" rx="1.5" fill="#48484A"/>
      <rect x="91" y="52" width="3" height="20" rx="1.5" fill="#48484A"/>
      <defs>
        <linearGradient id="screenGrad" x1="16" y1="22" x2="84" y2="142" gradientUnits="userSpaceOnUse">
          <stop stop-color="#007AFF" stop-opacity="0.3"/>
          <stop offset="1" stop-color="#00BCD4" stop-opacity="0.1"/>
        </linearGradient>
      </defs>
    </svg>
  </div>
  <div class="device-name">iPhone 6S</div>
  <div class="device-model">A1688 · Space Gray · 64 GB</div>
  <div class="version-badge"><i class="fas fa-circle-check"></i> V5 DELUXE · 15.5</div>
</div>

<!-- STORAGE BAR -->
<div class="section">
  <div class="section-label">Depolama</div>
  <div class="storage-section">
    <div class="storage-bar-wrap">
      <div class="storage-bar-label">
        <strong>64 GB</strong>
        <span>42,3 GB kullanıldı</span>
      </div>
      <div class="storage-track">
        <div class="storage-seg seg-os"></div>
        <div class="storage-seg seg-apps"></div>
        <div class="storage-seg seg-data"></div>
        <div class="storage-seg seg-media"></div>
        <div class="storage-seg seg-free"></div>
      </div>
      <div class="storage-legend">
        <div class="legend-item"><div class="legend-dot" style="background:#007AFF;"></div>iOS</div>
        <div class="legend-item"><div class="legend-dot" style="background:#30D158;"></div>Uygulamalar</div>
        <div class="legend-item"><div class="legend-dot" style="background:#FFD60A;"></div>Veriler</div>
        <div class="legend-item"><div class="legend-dot" style="background:#FF9F0A;"></div>Medya</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(255,255,255,0.2);"></div>Boş</div>
      </div>
    </div>
  </div>
</div>

<!-- SYSTEM INFO -->
<div class="section">
  <div class="section-label">Yazılım</div>
  <div class="list-group">
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(0,122,255,0.15);">
        <i class="fab fa-apple" style="color:#007AFF;"></i>
      </div>
      <span class="row-label">Sürüm</span>
      <span class="row-value cyan">15.5 DELUXE</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(255,214,10,0.15);">
        <i class="fas fa-code-branch" style="color:#FFD60A;"></i>
      </div>
      <span class="row-label">Sunucu Sürümü</span>
      <span class="row-value">5.70</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(48,209,88,0.15);">
        <i class="fab fa-php" style="color:#30D158;"></i>
      </div>
      <span class="row-label">PHP Servis Port</span>
      <span class="row-value mono">57249</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(0,188,212,0.15);">
        <i class="fas fa-database" style="color:#00BCD4;"></i>
      </div>
      <span class="row-label">Havuz</span>
      <span class="row-value">"LOCALHOST"</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(255,159,10,0.15);">
        <i class="fas fa-microchip" style="color:#FF9F0A;"></i>
      </div>
      <span class="row-label">Çekirdek</span>
      <span class="row-value">Apple A9</span>
    </div>
  </div>
</div>

<!-- DEVICE INFO -->
<div class="section">
  <div class="section-label">Cihaz Bilgileri</div>
  <div class="list-group">
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(0,122,255,0.15);">
        <i class="fas fa-mobile-screen" style="color:#007AFF;"></i>
      </div>
      <span class="row-label">Model Adı</span>
      <span class="row-value">iPhone 6S</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(142,142,147,0.15);">
        <i class="fas fa-hashtag" style="color:#8E8E93;"></i>
      </div>
      <span class="row-label">Model Numarası</span>
      <span class="row-value mono">A1688</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(48,209,88,0.15);">
        <i class="fas fa-memory" style="color:#30D158;"></i>
      </div>
      <span class="row-label">RAM</span>
      <span class="row-value green">2 GB LPDDR4</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(255,214,10,0.15);">
        <i class="fas fa-display" style="color:#FFD60A;"></i>
      </div>
      <span class="row-label">Ekran</span>
      <span class="row-value">4.7" · 1334×750</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(255,69,58,0.15);">
        <i class="fas fa-camera" style="color:#FF453A;"></i>
      </div>
      <span class="row-label">Kamera</span>
      <span class="row-value">12 MP · 4K</span>
    </div>
    <div class="list-row tappable" onclick="copySerial()">
      <div class="row-icon-wrap" style="background:rgba(0,188,212,0.15);">
        <i class="fas fa-barcode" style="color:#00BCD4;"></i>
      </div>
      <span class="row-label">Seri Numarası</span>
      <span class="row-value mono cyan" id="serialVal">F17QK4YNGRWP</span>
      <i class="fas fa-chevron-right row-chevron"></i>
    </div>
    <div class="list-row tappable" onclick="copyIMEI()">
      <div class="row-icon-wrap" style="background:rgba(255,159,10,0.15);">
        <i class="fas fa-sim-card" style="color:#FF9F0A;"></i>
      </div>
      <span class="row-label">IMEI</span>
      <span class="row-value mono" id="imeiVal">355664070988751</span>
      <i class="fas fa-chevron-right row-chevron"></i>
    </div>
  </div>
</div>

<!-- NETWORK -->
<div class="section">
  <div class="section-label">Ağ</div>
  <div class="list-group">
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(0,122,255,0.15);">
        <i class="fas fa-wifi" style="color:#007AFF;"></i>
      </div>
      <span class="row-label">Wi-Fi Adresi</span>
      <span class="row-value mono">A4:C3:F0:12:3B:7E</span>
    </div>
    <div class="list-row">
      <div class="row-icon-wrap" style="background:rgba(48,209,88,0.15);">
        <i class="fas fa-bluetooth-b" style="color:#30D158;"></i>
      </div>
      <span class="row-label">Bluetooth</span>
      <span class="row-value mono">A4:C3:F0:12:3B:7F</span>
    </div>
  </div>
</div>

<!-- FAQ -->
<div class="section">
  <div class="section-label">Sıkça Sorulan Sorular</div>

  <div class="faq-item">
    <button class="faq-question">
      Bu hizmetin amacı nedir?
      <i class="fas fa-chevron-right faq-chevron"></i>
    </button>
    <div class="faq-answer">
      Baskıcı ebeveynlerin gazabından kurtulmak ve kendini burada rahat hissedebileceğin bir yer sunmak.
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-question">
      Daha fazla destek gerekiyor mu?
      <i class="fas fa-chevron-right faq-chevron"></i>
    </button>
    <div class="faq-answer" style="padding-bottom:14px;">
      <div class="btn-group">
        <a class="link-btn" href="https://t.me/+447599608079" target="_blank"><i class="fab fa-telegram"></i> Destek</a>
        <a class="link-btn" href="https://t.me/ViosTeam" target="_blank"><i class="fab fa-telegram"></i> Kanal</a>
        <a class="link-btn" href="https://github.com/zeedslowy" target="_blank"><i class="fab fa-github"></i> GitHub</a>
      </div>
    </div>
  </div>

</div>

<div class="bottom-pad"></div>

<!-- Copy toast -->
<div id="toast" style="
  position:fixed; bottom:100px; left:50%; transform:translateX(-50%) translateY(20px);
  background:rgba(44,44,46,0.95); backdrop-filter:blur(20px);
  border:0.5px solid rgba(255,255,255,0.12);
  color:#fff; font-size:14px; font-weight:500;
  padding:10px 20px; border-radius:20px;
  opacity:0; transition:all 0.25s ease; pointer-events:none;
  white-space:nowrap; z-index:999;
">Kopyalandı ✓</div>

<script>
// ── FAQ accordion ──
document.querySelectorAll('.faq-question').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.faq-item');
    const isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
  });
});

// ── Copy toast ──
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg + ' ✓';
  t.style.opacity = '1'; t.style.transform = 'translateX(-50%) translateY(0)';
  setTimeout(() => {
    t.style.opacity = '0'; t.style.transform = 'translateX(-50%) translateY(20px)';
  }, 1800);
}
function copySerial() {
  navigator.clipboard?.writeText(document.getElementById('serialVal').textContent);
  showToast('Seri No Kopyalandı');
}
function copyIMEI() {
  navigator.clipboard?.writeText(document.getElementById('imeiVal').textContent);
  showToast('IMEI Kopyalandı');
}
</script>
</body>
</html>
