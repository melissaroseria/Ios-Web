<?php
$logFiles = glob('logs/*.log');
$logFile = 'logs/' . date('Y-m-d') . '.log';
$logMessage = "[" . date('Y-m-d H:i:s') . "] IP: {$_SERVER['REMOTE_ADDR']} | Sayfa açıldı\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Log Kayıtları</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../ios.css">
</head>
<body>
<div class="ios-nav">
  <a class="nav-back" href="../config.php"><i class="fas fa-chevron-left"></i> Ayarlar</a>
  <span class="nav-title">Log Kayıtları</span>
  <div class="nav-right">
    <span style="color:var(--blue);font-size:14px;cursor:pointer;" onclick="clearLogs()"></span>
  </div>
</div>
<div class="ios-page">

  <div class="ios-hero">
    <div class="ios-hero-icon" style="background:rgba(94,92,230,0.15);">📋</div>
    <h2><?= count($logFiles) ?> Log Dosyası</h2>
    <p>Sistem erişim geçmişi ve aktivite kayıtları.</p>
  </div>

  <div class="ios-section">
    <div class="ios-section-label">Log Dosyaları</div>
    <?php if(empty($logFiles)): ?>
    <div style="margin:0 16px;background:var(--bg2);border-radius:12px;padding:24px;text-align:center;border:0.5px solid var(--border);">
      <div style="font-size:32px;margin-bottom:8px;">📭</div>
      <p style="color:var(--text2);font-size:14px;">Henüz log kaydı bulunmuyor.</p>
    </div>
    <?php else: ?>
    <div class="ios-group">
      <?php foreach(array_reverse($logFiles) as $file): ?>
      <?php $fname=basename($file); $lineCount=count(file($file)); ?>
      <div class="ios-row tappable" onclick="openLog('<?= $fname ?>')">
        <div class="ios-icon" style="background:rgba(94,92,230,0.15);">📒</div>
        <div style="flex:1;">
          <div style="font-size:15px;"><?= $fname ?></div>
          <div style="font-size:12px;color:var(--text2);margin-top:2px;"><?= $lineCount ?> kayıt</div>
        </div>
        <i class="fas fa-chevron-right ios-chevron"></i>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</div>

<!-- Log Modal -->
<div class="ios-modal-overlay" id="logModal">
  <div class="ios-modal-sheet" style="max-height:75vh;display:flex;flex-direction:column;">
    <div class="ios-modal-handle"></div>
    <div class="ios-modal-title" id="logModalTitle">Log</div>
    <div style="flex:1;overflow-y:auto;padding:12px 16px 20px;">
      <pre id="logContent" style="font-size:12px;font-family:'SF Mono','Courier New',monospace;color:#30D158;line-height:1.7;white-space:pre-wrap;word-break:break-all;"></pre>
    </div>
    <div style="padding:0 16px 8px;">
      <button class="ios-btn plain" onclick="document.getElementById('logModal').classList.remove('open')">Kapat</button>
    </div>
  </div>
</div>

<div id="ios-toast"></div>
<script>
function openLog(fname) {
  document.getElementById('logModalTitle').textContent = fname;
  document.getElementById('logContent').textContent = 'Yükleniyor...';
  document.getElementById('logModal').classList.add('open');
  fetch('read_log.php?file=' + encodeURIComponent(fname))
    .then(r=>r.text())
    .then(t=>{ document.getElementById('logContent').textContent = t || '(Boş dosya)'; })
    .catch(()=>{ document.getElementById('logContent').textContent = 'Dosya okunamadı.'; });
}
document.getElementById('logModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
function clearLogs() {
  const t=document.getElementById('ios-toast');
  t.textContent='🗑️ Loglar temizlendi'; t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),2000);
}
</script>
</body>
</html>