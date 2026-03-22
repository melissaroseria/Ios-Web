<?php
/* ── Log sistemi ── */
$logDir = 'charts/logs/logs';
if (!is_dir($logDir)) mkdir($logDir, 0777, true);
$ipAddress  = $_SERVER['REMOTE_ADDR'] ?? 'Bilinmeyen IP';
$logFile    = $logDir . '/' . date('Y-m-d') . '.log';
$logMessage = "[" . date('Y-m-d H:i:s') . "] IP: $ipAddress | Sayfa yenilendi\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

/* ── Türkçe tarih ── */
$days   = ['Sunday'=>'Pazar','Monday'=>'Pazartesi','Tuesday'=>'Salı',
           'Wednesday'=>'Çarşamba','Thursday'=>'Perşembe','Friday'=>'Cuma','Saturday'=>'Cumartesi'];
$months = ['January'=>'Ocak','February'=>'Şubat','March'=>'Mart','April'=>'Nisan',
           'May'=>'Mayıs','June'=>'Haziran','July'=>'Temmuz','August'=>'Ağustos',
           'September'=>'Eylül','October'=>'Ekim','November'=>'Kasım','December'=>'Aralık'];
$now      = new DateTime();
$timeStr  = $now->format('H:i');
$dayTR    = $days[$now->format('l')] ?? $now->format('l');
$monthTR  = $months[$now->format('F')] ?? $now->format('F');
$dateTR   = $dayTR . ', ' . $now->format('d') . ' ' . $monthTR;

/*
 * ── İkon kaynağı: img.icons8.com CDN ──
 *
 * ÖNCELIK SIRASI (her ikon için):
 *   1. Kendi sunucundaki PNG  → yüklenirse kullanılır
 *   2. fluency stili CDN      → gerçek iOS tarzı 3D renkli ikon
 *   3. color stili CDN        → düz renkli yedek
 *
 * "fluency" = Microsoft Fluent 3D — iOS ikon tasarımına en yakın stil
 * "color"   = düz renkli yedek
 *
 * bg: resim tamamen bozulsa görünecek arka plan rengi (iOS ikon rengi)
 */

// fluency = 3D iOS benzeri ikonlar (en güzel)
$F   = 'https://img.icons8.com/fluency/100';
// color  = düz yedek
$C   = 'https://img.icons8.com/color/100';

/* ── Uygulamalar ── */
$apps = [
  [
    // Terminal / Rehber — siyah terminal ekranı, yeşil yazı tarzı
    'href'  => 'run/shell/index.php',
    'img'   => 'run/assets/img/term.png',
    'cdn'   => $F . '/console.png',
    'cdn2'  => $C . '/console.png',
    'label' => 'Rehber',
    'bg'    => '#1c1c1e',
  ],
  [
    // SSH / Sudo — kilit + anahtar, kırmızı
    'href'  => 'run/sudo/index.php',
    'img'   => 'run/assets/img/ssh.png',
    'cdn'   => $F . '/lock-2.png',
    'cdn2'  => $C . '/lock-2.png',
    'label' => 'Sudo',
    'bg'    => '#ff3b30',
  ],
  [
    // Tarayıcı — Safari benzeri compass, mavi
    'href'  => 'run/browser/index.php',
    'img'   => 'run/assets/img/web.png',
    'cdn'   => $F . '/safari--v1.png',
    'cdn2'  => $C . '/google-chrome.png',
    'label' => 'Tarayıcı',
    'bg'    => '#0a84ff',
  ],
  [
    // Galeri — iOS Photos benzeri
    'href'  => 'run/gallery/index.php',
    'img'   => 'run/assets/img/jpg.png',
    'cdn'   => $F . '/pictures-folder.png',
    'cdn2'  => $C . '/pictures-folder.png',
    'label' => 'Galeri',
    'bg'    => '#ff9500',
  ],
  [
    // RAR / Arşiv — klasör/zip
    'href'  => 'run/rars/index.php',
    'img'   => 'run/assets/img/rars.png',
    'cdn'   => $F . '/zip.png',
    'cdn2'  => $C . '/zip.png',
    'label' => 'RAR',
    'bg'    => '#ff9f0a',
  ],
  [
    // Telegram — orijinal Telegram ikonu
    'href'  => 'run/bot/index.php',
    'img'   => 'run/assets/img/bot.png',
    'cdn'   => $F . '/telegram-app.png',
    'cdn2'  => $C . '/telegram-app.png',
    'label' => 'Telgraq',
    'bg'    => '#2aabee',
  ],
  [
    // Postman benzeri — API test
    'href'  => 'run/post/index.php',
    'img'   => 'run/assets/img/post.png',
    'cdn'   => $F . '/postman-api.png',
    'cdn2'  => $C . '/json.png',
    'label' => 'PostX',
    'bg'    => '#ef5c00',
  ],
  [
    // iOS Ayarlar — tıpa tıp Apple Settings ikonu
    'href'  => 'config.php',
    'img'   => 'run/assets/img/set.png',
    'cdn'   => $F . '/ios-settings.png',
    'cdn2'  => $C . '/settings.png',
    'label' => 'Ayarlar',
    'bg'    => '#8e8e93',
  ],
  [
    // Notlar — iOS Notes sarı ikon
    'href'  => 'run/notes/index.php',
    'img'   => 'run/assets/img/main.png',
    'cdn'   => $F . '/notes.png',
    'cdn2'  => $C . '/note.png',
    'label' => 'Notlar',
    'bg'    => '#ffd60a',
  ],
  [
    // Kart / Check — kredi kartı
    'href'  => 'run/check/index.php',
    'img'   => 'run/assets/img/cc.png',
    'cdn'   => $F . '/bank-card-front-side.png',
    'cdn2'  => $C . '/bank-card-front-side.png',
    'label' => 'Check',
    'bg'    => '#30d158',
  ],
];

/* ── Dock ── */
$dock = [
  [
    'href'   => 'index.php',
    'img'    => 'run/assets/src/home.png',
    'cdn'    => $F . '/home-page.png',
    'cdn2'   => $C . '/home.png',
    'label'  => 'Ana Sayfa',
    'active' => true,
    'bg'     => '#0a84ff',
  ],
  [
    'href'   => 'run/browser/index.php',
    'img'    => 'run/assets/img/web.png',
    'cdn'    => $F . '/safari--v1.png',
    'cdn2'   => $C . '/google-chrome.png',
    'label'  => 'Tarayıcı',
    'active' => false,
    'bg'     => '#0a84ff',
  ],
  [
    'href'   => 'run/bot/index.php',
    'img'    => 'run/assets/img/bot.png',
    'cdn'    => $F . '/telegram-app.png',
    'cdn2'   => $C . '/telegram-app.png',
    'label'  => 'Telgraq',
    'active' => false,
    'bg'     => '#2aabee',
  ],
  [
    'href'   => 'config.php',
    'img'    => 'run/assets/img/set.png',
    'cdn'    => $F . '/ios-settings.png',
    'cdn2'   => $C . '/settings.png',
    'label'  => 'Ayarlar',
    'active' => false,
    'bg'     => '#8e8e93',
  ],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>İ FRUİT</title>

  <!-- ANA CSS — Arkaplan, ikonlar, dock hepsi buradan -->
  <link rel="stylesheet" href="../css/boot-mains.css">

  <style>
    /* ── Sadece bu sayfaya özgü küçük eklemeler ── */

    /* Scroll wrapper — CSS'te tanımlı .home-screen-wrap'ı kullanıyoruz,
       ama boot-mains.css yoksa diye burada da tanımlıyoruz */
    .page-wrap {
      position: fixed;
      inset: 0;
      z-index: 5;
      overflow-y: auto;
      overflow-x: hidden;
      -webkit-overflow-scrolling: touch;
      /* Saat + arama için üstten boşluk */
      padding-top: calc(env(safe-area-inset-top, 44px) + 192px);
      padding-bottom: 148px;
    }
    .page-wrap::-webkit-scrollbar { display: none; }
  </style>
</head>
<body>

<!-- ══ STATUS BAR ══ -->
<div class="status-bar">
  <span class="status-time" id="stime"><?= $timeStr ?></span>
  <div class="status-icons">
    <!-- Sinyal -->
    <div class="signal-bars">
      <span></span><span></span><span></span><span></span>
    </div>
    <!-- WiFi -->
    <div class="wifi-icon"><div class="wifi-dot"></div></div>
    <!-- Batarya -->
    <div class="battery">
      <div class="battery-shell"><div class="battery-fill"></div></div>
    </div>
  </div>
</div>

<!-- ══ SAAT & TARİH ══ -->
<div class="lock-clock">
  <div class="lock-time" id="lclock"><?= $timeStr ?></div>
  <div class="lock-date"><?= $dateTR ?></div>
</div>

<!-- ══ ANA EKRAN (scroll'lanan alan) ══ -->
<div class="page-wrap">

  <!-- Arama çubuğu -->
  <div class="ios-search">
    <div class="ios-search-inner">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
           stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <span>Ara</span>
    </div>
  </div>

  <!-- Uygulama grid -->
  <div class="home-screen">
    <?php foreach ($apps as $app): ?>
    <div class="app-icon"
         onclick="window.location.href='<?= htmlspecialchars($app['href']) ?>'">
      <div class="icon-shell" style="background:<?= $app['bg'] ?>">
        <img src="<?= htmlspecialchars($app['cdn']) ?>"
             alt="<?= htmlspecialchars($app['label']) ?>"
             data-fb1="<?= htmlspecialchars($app['cdn2']) ?>"
             data-fb2="<?= htmlspecialchars($app['img']) ?>"
             onerror="
               var fb=this.dataset.fb1;
               if(fb&&this.src!==fb){this.src=fb;this.dataset.fb1='';return;}
               var fb2=this.dataset.fb2;
               if(fb2&&this.src!==fb2){this.src=fb2;}
             "
             loading="lazy">
      </div>
      <span><?= htmlspecialchars($app['label']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Sayfa noktaları -->
  <div class="page-dots">
    <div class="page-dot active"></div>
    <div class="page-dot"></div>
    <div class="page-dot"></div>
  </div>

</div><!-- /page-wrap -->

<!-- ══ DOCK / ALT MENÜ ══ -->
<div class="bottom-menu">
  <div class="dock-pill">
    <?php foreach ($dock as $d): ?>
    <div class="bottom-menu-item"
         onclick="window.location.href='<?= htmlspecialchars($d['href']) ?>'">
      <div class="icon-shell" style="background:<?= $d['bg'] ?>">
        <img src="<?= htmlspecialchars($d['cdn']) ?>"
             alt="<?= htmlspecialchars($d['label']) ?>"
             data-fb1="<?= htmlspecialchars($d['cdn2']) ?>"
             data-fb2="<?= htmlspecialchars($d['img']) ?>"
             onerror="
               var fb=this.dataset.fb1;
               if(fb&&this.src!==fb){this.src=fb;this.dataset.fb1='';return;}
               var fb2=this.dataset.fb2;
               if(fb2&&this.src!==fb2){this.src=fb2;}
             "
             loading="lazy">
      </div>
      <span><?= htmlspecialchars($d['label']) ?></span>
      <?php if ($d['active']): ?>
        <div class="dock-dot"></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- iOS Home Indicator çubuğu -->
<div class="home-indicator"></div>

<!-- ══ CANLI SAAT ══ -->
<script>
  (function() {
    function pad(n) { return String(n).padStart(2, '0'); }
    function tick() {
      var d = new Date();
      var t = pad(d.getHours()) + ':' + pad(d.getMinutes());
      var s  = document.getElementById('stime');
      var lc = document.getElementById('lclock');
      if (s)  s.textContent  = t;
      if (lc) lc.textContent = t;
    }
    // Bir sonraki dakika başına senkronize et
    var delay = (60 - new Date().getSeconds()) * 1000;
    setTimeout(function() { tick(); setInterval(tick, 60000); }, delay);
  })();
</script>

</body>
</html>