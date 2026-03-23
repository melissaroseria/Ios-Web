<?php
$directories = [
    '../../run/gallery/uploads',
    '../../run/notes/notes',
    '../../run/sudo/uploads',
    '../../run/post/settings/logs',
    '../../run/bot/bot',
];
$dirLabels = ['Galeri','Notlar','Sudo Yüklemeler','Post Logları','Bot'];
$dirIcons  = ['🖼️','📝','⬆️','📋','🤖'];
$dirColors = ['#007AFF','#30D158','#FF9F0A','#5E5CE6','#BF5AF2'];

$totalFiles = 0; $dirData = [];
foreach ($directories as $i => $dir) {
    $count = 0; $files = [];
    if (is_dir($dir)) { $all = array_diff(scandir($dir),['.','..']); $count=count($all); $files=array_values($all); }
    $totalFiles += $count;
    $dirData[] = ['dir'=>$dir,'label'=>$dirLabels[$i],'icon'=>$dirIcons[$i],'color'=>$dirColors[$i],'count'=>$count,'files'=>$files];
}
$deleted = false;
if (isset($_POST['deleteFiles'])) {
    foreach ($directories as $dir) { if (is_dir($dir)) { foreach (array_diff(scandir($dir),['.','..']) as $f) @unlink("$dir/$f"); } }
    $deleted = true; $totalFiles = 0; foreach ($dirData as &$d) { $d['count']=0; $d['files']=[]; }
}
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Disk Yönetimi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../ios.css">
</head>
<body>
<div class="ios-nav">
  <a class="nav-back" href="../config.php"><i class="fas fa-chevron-left"></i> Ayarlar</a>
  <span class="nav-title">Disk Yönetimi</span>
  <div class="nav-right"></div>
</div>
<div class="ios-page">

  <div class="ios-hero">
    <div class="ios-hero-icon" style="background:rgba(255,59,48,0.15);">🗑️</div>
    <h2><?= $totalFiles ?> Dosya</h2>
    <p>Önbellek ve geçici dosyaları temizleyerek yer açabilirsiniz.</p>
  </div>

  <?php if($deleted): ?>
  <div style="margin:0 16px 16px;">
    <div style="background:rgba(48,209,88,0.12);border:0.5px solid rgba(48,209,88,0.35);border-radius:14px;padding:14px 16px;display:flex;align-items:center;gap:10px;">
      <span style="font-size:20px;">✅</span>
      <span style="font-size:15px;font-weight:500;">Tüm dosyalar başarıyla temizlendi.</span>
    </div>
  </div>
  <?php endif; ?>

  <div class="ios-section">
    <div class="ios-section-label">Depolama Dağılımı</div>
    <div class="ios-storage-bar">
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
        <strong style="font-size:15px;">Toplam <?= $totalFiles ?> öğe</strong>
        <span style="font-size:13px;color:var(--text2);">5 klasör</span>
      </div>
      <div class="storage-track">
        <?php foreach($dirData as $d): $w=$totalFiles>0?round($d['count']/$totalFiles*100):0; ?>
          <div class="storage-seg" style="width:<?= max($w,2) ?>%;background:<?= $d['color'] ?>;"></div>
        <?php endforeach; ?>
        <?php if($totalFiles==0): ?><div class="storage-seg" style="width:100%;background:rgba(255,255,255,0.1);"></div><?php endif; ?>
      </div>
      <div class="storage-legend">
        <?php foreach($dirData as $d): ?>
          <div class="legend-item"><div class="legend-dot" style="background:<?= $d['color'] ?>;"></div><?= $d['label'] ?> (<?= $d['count'] ?>)</div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="ios-section">
    <div class="ios-section-label">Klasörler</div>
    <div class="ios-group">
      <?php foreach($dirData as $d): $lid='dir-'.$d['label']; ?>
      <div class="ios-row tappable" onclick="toggleDir('<?= $lid ?>')">
        <div class="ios-icon" style="background:<?= $d['color'] ?>22;font-size:18px;"><?= $d['icon'] ?></div>
        <span class="ios-row-label"><?= $d['label'] ?></span>
        <span class="ios-row-value"><?= $d['count'] ?> öğe</span>
        <i class="fas fa-chevron-right ios-chevron" id="chev-<?= $d['label'] ?>" style="transition:transform 0.2s;"></i>
      </div>
      <div id="<?= $lid ?>" style="display:none;background:rgba(0,0,0,0.25);padding:8px 16px 8px 62px;border-top:0.5px solid var(--border);">
        <?php if(empty($d['files'])): ?>
          <span style="font-size:13px;color:var(--text2);">Boş klasör</span>
        <?php else: foreach($d['files'] as $f): ?>
          <div style="font-size:13px;color:var(--text2);padding:3px 0;border-bottom:0.5px solid rgba(255,255,255,0.05);">📄 <?= htmlspecialchars($f) ?></div>
        <?php endforeach; endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="ios-section">
    <div style="margin:8px 16px 0;">
      <button class="ios-btn danger" onclick="document.getElementById('confirmModal').classList.add('open')">
        <i class="fas fa-trash-alt"></i>  Tüm Önbelleği Temizle
      </button>
    </div>
    <div class="ios-section-footer">Bu işlem geri alınamaz. Galeri, notlar ve geçici dosyalar silinecektir.</div>
  </div>

</div>

<div class="ios-modal-overlay" id="confirmModal">
  <div class="ios-modal-sheet">
    <div class="ios-modal-handle"></div>
    <div class="ios-modal-title">Disk Temizliği</div>
    <div class="ios-modal-body">
      <div style="text-align:center;padding:8px 0 20px;">
        <div style="font-size:48px;margin-bottom:12px;">⚠️</div>
        <p style="font-size:15px;color:var(--text2);line-height:1.6;"><?= $totalFiles ?> dosya silinecek. Bu işlem geri alınamaz.</p>
      </div>
      <form method="POST"><button type="submit" name="deleteFiles" class="ios-btn danger">Temizle</button></form>
      <div style="margin-top:10px;"><button class="ios-btn plain" onclick="document.getElementById('confirmModal').classList.remove('open')">İptal</button></div>
    </div>
  </div>
</div>
<div id="ios-toast"></div>
<script>
function toggleDir(id) {
  const el=document.getElementById(id), label=id.replace('dir-',''), chev=document.getElementById('chev-'+label);
  const open=el.style.display!=='none'; el.style.display=open?'none':'block';
  if(chev) chev.style.transform=open?'':'rotate(90deg)';
}
document.getElementById('confirmModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});
<?php if($deleted): ?>document.getElementById('ios-toast').textContent='✅ Temizlendi';document.getElementById('ios-toast').classList.add('show');setTimeout(()=>document.getElementById('ios-toast').classList.remove('show'),2200);<?php endif; ?>
</script>
</body>
</html>