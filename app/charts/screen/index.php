<?php
$targetDir = "../../run/assets/user/src/";
$wallpaperPath = $targetDir . "background.jpg";
$profileImagePath = "../../run/assets/user/plus/users.png";
$uploaded = false;
if ($_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_FILES['wallpaper']) && $_FILES['wallpaper']['error']==UPLOAD_ERR_OK)
        move_uploaded_file($_FILES['wallpaper']['tmp_name'], $wallpaperPath);
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error']==UPLOAD_ERR_OK)
        move_uploaded_file($_FILES['profileImage']['tmp_name'], $profileImagePath);
    $uploaded = true;
    header("Location: index.php?success=1"); exit;
}
$wallpaperSrc = file_exists($wallpaperPath) ? $wallpaperPath : '../../run/assets/default/background.jpg';
$profileImageSrc = file_exists($profileImagePath) ? $profileImagePath : '../../run/assets/default/users.png';
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Ekran ve Parlaklık</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../ios.css">
</head>
<body>
<div class="ios-nav">
  <a class="nav-back" href="../config.php"><i class="fas fa-chevron-left"></i> Ayarlar</a>
  <span class="nav-title">Ekran ve Parlaklık</span>
  <div class="nav-right"></div>
</div>
<div class="ios-page">

  <!-- Preview -->
  <div style="margin:16px 16px 0;border-radius:18px;overflow:hidden;border:0.5px solid var(--border);position:relative;height:200px;">
    <img src="<?= htmlspecialchars($wallpaperSrc) ?>" id="wallpaperPreview"
         style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.25);display:flex;align-items:center;justify-content:center;">
      <div style="text-align:center;">
        <img src="<?= htmlspecialchars($profileImageSrc) ?>" id="profilePreview"
             style="width:64px;height:64px;border-radius:50%;border:3px solid rgba(255,255,255,0.8);object-fit:cover;"
             onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 64 64\'><circle cx=\'32\' cy=\'32\' r=\'32\' fill=\'%23444\'/></svg>'">
        <div style="color:white;font-size:14px;font-weight:600;margin-top:6px;text-shadow:0 1px 4px rgba(0,0,0,0.6);">Önizleme</div>
      </div>
    </div>
  </div>

  <?php if(isset($_GET['success'])): ?>
  <div style="margin:12px 16px 0;">
    <div style="background:rgba(48,209,88,0.12);border:0.5px solid rgba(48,209,88,0.35);border-radius:12px;padding:12px 16px;font-size:14px;color:#30D158;display:flex;align-items:center;gap:8px;">
      <i class="fas fa-check-circle"></i> Değişiklikler kaydedildi.
    </div>
  </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="uploadForm">

    <!-- Duvar kağıdı -->
    <div class="ios-section">
      <div class="ios-section-label">Duvar Kağıdı</div>
      <div class="ios-group">
        <label class="ios-row tappable" for="wallpaperInput">
          <div class="ios-icon" style="background:rgba(175,82,222,0.15);"><i class="fas fa-image" style="color:#AF52DE;"></i></div>
          <span class="ios-row-label">Duvar Kağıdı Seç</span>
          <span class="ios-row-value" id="wallpaperName">Seçilmedi</span>
          <i class="fas fa-chevron-right ios-chevron"></i>
        </label>
        <input type="file" id="wallpaperInput" name="wallpaper" accept="image/*" style="display:none;" onchange="previewFile(this,'wallpaperPreview','wallpaperName')">
      </div>
    </div>

    <!-- Profil resmi -->
    <div class="ios-section">
      <div class="ios-section-label">Profil Resmi</div>
      <div class="ios-group">
        <label class="ios-row tappable" for="profileInput">
          <div class="ios-icon" style="background:rgba(0,122,255,0.15);"><i class="fas fa-user-circle" style="color:#007AFF;"></i></div>
          <span class="ios-row-label">Profil Fotoğrafı Seç</span>
          <span class="ios-row-value" id="profileName">Seçilmedi</span>
          <i class="fas fa-chevron-right ios-chevron"></i>
        </label>
        <input type="file" id="profileInput" name="profileImage" accept="image/*" style="display:none;" onchange="previewFile(this,'profilePreview','profileName')">
      </div>
    </div>

    <!-- Parlaklık / Görünüm -->
    <div class="ios-section">
      <div class="ios-section-label">Görünüm</div>
      <div class="ios-group">
        <div class="ios-row">
          <div class="ios-icon" style="background:rgba(255,214,10,0.15);">☀️</div>
          <span class="ios-row-label">Parlaklık</span>
          <input type="range" min="0" max="100" value="80"
            style="width:120px;accent-color:var(--blue);" oninput="this.nextElementSibling.textContent=this.value+'%'">
          <span style="font-size:13px;color:var(--text2);min-width:36px;text-align:right;">80%</span>
        </div>
        <div class="ios-row">
          <div class="ios-icon" style="background:rgba(0,0,0,0.3);">🌙</div>
          <span class="ios-row-label">Karanlık Mod</span>
          <button type="button" class="ios-toggle on" onclick="this.classList.toggle('on')"></button>
        </div>
        <div class="ios-row">
          <div class="ios-icon" style="background:rgba(0,122,255,0.15);">🔤</div>
          <span class="ios-row-label">Büyük Metin</span>
          <button type="button" class="ios-toggle" onclick="this.classList.toggle('on')"></button>
        </div>
      </div>
    </div>

    <!-- Uygula -->
    <div class="ios-section">
      <div style="margin:8px 16px 0;">
        <button type="submit" class="ios-btn primary"><i class="fas fa-check"></i>  Değişiklikleri Uygula</button>
      </div>
    </div>

  </form>

</div>
<div id="ios-toast"></div>
<script>
function previewFile(input, previewId, nameId) {
  const file = input.files[0];
  if(!file) return;
  document.getElementById(nameId).textContent = file.name.length>16 ? file.name.substring(0,14)+'…' : file.name;
  const reader = new FileReader();
  reader.onload = e => document.getElementById(previewId).src = e.target.result;
  reader.readAsDataURL(file);
}
<?php if(isset($_GET['success'])): ?>
const t=document.getElementById('ios-toast'); t.textContent='✅ Kaydedildi'; t.classList.add('show');
setTimeout(()=>t.classList.remove('show'),2200);
<?php endif; ?>
</script>
</body>
</html>