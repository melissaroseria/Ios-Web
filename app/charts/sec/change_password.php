<?php session_start();
$message = ''; $success = false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$new) $message = 'Lütfen geçerli bir şifre girin.';
    elseif ($new !== $confirm) $message = 'Şifreler eşleşmiyor.';
    elseif (strlen($new) < 4) $message = 'Şifre en az 4 karakter olmalıdır.';
    else { file_put_contents("../../../src/pass.txt", $new); $success=true; $message='Şifreniz başarıyla güncellendi.'; }
}
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Şifreyi Değiştir</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../ios.css">
</head>
<body>
<div class="ios-nav">
  <a class="nav-back" href="index.php"><i class="fas fa-chevron-left"></i> Güvenlik</a>
  <span class="nav-title">Şifreyi Değiştir</span>
  <div class="nav-right"></div>
</div>
<div class="ios-page">

  <div class="ios-hero">
    <div class="ios-hero-icon" style="background:rgba(191,90,242,0.15);">🔑</div>
    <h2>Yeni Şifre</h2>
    <p>Güçlü bir şifre seçin ve güvenliğinizi artırın.</p>
  </div>

  <?php if($message): ?>
  <div style="margin:0 16px 16px;">
    <div style="background:<?= $success?'rgba(48,209,88,0.12)':'rgba(255,59,48,0.12)' ?>;border:0.5px solid <?= $success?'rgba(48,209,88,0.4)':'rgba(255,59,48,0.4)' ?>;border-radius:12px;padding:13px 16px;font-size:14px;color:<?= $success?'#30D158':'#FF3B30' ?>;display:flex;align-items:center;gap:8px;">
      <i class="fas <?= $success?'fa-check-circle':'fa-exclamation-circle' ?>"></i>
      <?= htmlspecialchars($message) ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="ios-section">
    <div class="ios-section-label">Yeni Şifre</div>
    <form method="POST" style="display:flex;flex-direction:column;gap:10px;margin:0 16px;">
      <div style="position:relative;">
        <input class="ios-input" type="password" name="new_password" id="newPass" placeholder="Yeni şifre" required>
        <i class="fas fa-eye" onclick="toggleVis('newPass',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text2);cursor:pointer;font-size:15px;"></i>
      </div>
      <div style="position:relative;">
        <input class="ios-input" type="password" name="confirm_password" id="confPass" placeholder="Şifreyi tekrar girin" required>
        <i class="fas fa-eye" onclick="toggleVis('confPass',this)" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text2);cursor:pointer;font-size:15px;"></i>
      </div>

      <!-- Strength meter -->
      <div id="strengthWrap" style="display:none;margin-top:2px;">
        <div style="height:4px;background:rgba(255,255,255,0.08);border-radius:2px;overflow:hidden;">
          <div id="strengthBar" style="height:100%;border-radius:2px;transition:width 0.3s,background 0.3s;width:0%;"></div>
        </div>
        <span id="strengthLabel" style="font-size:12px;color:var(--text2);margin-top:4px;display:block;"></span>
      </div>

      <button type="submit" class="ios-btn primary" style="margin-top:4px;"><i class="fas fa-check"></i>  Güncelle</button>
    </form>
    <div class="ios-section-footer">Şifre en az 4 karakter olmalıdır. Şifrenizi güvenli bir yerde saklayın.</div>
  </div>

</div>
<div id="ios-toast"></div>
<script>
function toggleVis(id, icon) {
  const el = document.getElementById(id);
  const show = el.type==='password';
  el.type = show?'text':'password';
  icon.className = show?'fas fa-eye-slash':'fas fa-eye';
  icon.style.cssText = icon.style.cssText;
}
document.getElementById('newPass').addEventListener('input', function(){
  const v = this.value; const w = document.getElementById('strengthWrap');
  const bar = document.getElementById('strengthBar'); const lbl = document.getElementById('strengthLabel');
  if(!v){ w.style.display='none'; return; }
  w.style.display='block';
  let score=0;
  if(v.length>=6) score++; if(v.length>=10) score++;
  if(/[A-Z]/.test(v)) score++; if(/[0-9]/.test(v)) score++; if(/[^a-zA-Z0-9]/.test(v)) score++;
  const levels=[{p:20,c:'#FF3B30',l:'Çok Zayıf'},{p:40,c:'#FF9F0A',l:'Zayıf'},{p:60,c:'#FFD60A',l:'Orta'},{p:80,c:'#30D158',l:'Güçlü'},{p:100,c:'#007AFF',l:'Çok Güçlü'}];
  const lvl=levels[Math.min(score,4)];
  bar.style.width=lvl.p+'%'; bar.style.background=lvl.c; lbl.textContent=lvl.l; lbl.style.color=lvl.c;
});
<?php if($success): ?>
const t=document.getElementById('ios-toast'); t.textContent='🔑 Şifre güncellendi'; t.classList.add('show');
setTimeout(()=>t.classList.remove('show'),2200);
<?php endif; ?>
</script>
</body>
</html>
