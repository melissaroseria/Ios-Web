<?php session_start();
if (!file_exists("../../../src/pass.txt")) { header("Location: password.php"); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password'])) {
    $entered = $_POST['password'];
    $stored  = trim(file_get_contents("../../../src/pass.txt"));
    if ($entered===$stored) { $_SESSION['loggedin']=true; header("Location: ../../../src/main.php"); exit; }
    else $error = 'Şifre yanlış, tekrar deneyin.';
}
?><!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Güvenlik</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../ios.css">
</head>
<body>
<div class="ios-nav">
  <a class="nav-back" href="../config.php"><i class="fas fa-chevron-left"></i> Ayarlar</a>
  <span class="nav-title">Güvenlik</span>
  <div class="nav-right"></div>
</div>
<div class="ios-page">

  <div class="ios-hero">
    <div class="ios-hero-icon" style="background:rgba(48,209,88,0.15);">🔐</div>
    <h2>Güvenlik Ayarları</h2>
    <p>Şifrenizi yönetin ve erişim güvenliğinizi kontrol edin.</p>
  </div>

  <!-- Şifre değiştir -->
  <div class="ios-section">
    <div class="ios-section-label">Şifre</div>
    <div class="ios-group">
      <a href="change_password.php" class="ios-row tappable">
        <div class="ios-icon" style="background:rgba(48,209,88,0.18);"><i class="fas fa-key" style="color:#30D158;"></i></div>
        <span class="ios-row-label">Şifreyi Değiştir</span>
        <i class="fas fa-chevron-right ios-chevron"></i>
      </a>
    </div>
    <div class="ios-section-footer">Şifrenizi düzenli aralıklarla güncelleyin.</div>
  </div>

  <!-- Giriş formu -->
  <div class="ios-section">
    <div class="ios-section-label">Hızlı Giriş</div>
    <?php if($error): ?>
    <div style="margin:0 16px 12px;">
      <div style="background:rgba(255,59,48,0.12);border:0.5px solid rgba(255,59,48,0.35);border-radius:12px;padding:12px 16px;font-size:14px;color:#FF3B30;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    </div>
    <?php endif; ?>
    <form method="POST" style="margin:0 16px;">
      <input class="ios-input" type="password" name="password" placeholder="Şifrenizi girin" autocomplete="current-password" style="margin-bottom:10px;">
      <button type="submit" class="ios-btn success"><i class="fas fa-unlock-alt"></i>  Giriş Yap</button>
    </form>
  </div>

  <!-- Info -->
  <div class="ios-section">
    <div class="ios-section-label">Bilgilendirme</div>
    <div class="ios-group">
      <div class="ios-row">
        <div class="ios-icon" style="background:rgba(255,214,10,0.15);">⚠️</div>
        <span class="ios-row-label" style="font-size:14px;color:var(--text2);line-height:1.5;">Şifrenizi unutursanız verilere erişim imkânı yoktur.</span>
      </div>
      <div class="ios-row">
        <div class="ios-icon" style="background:rgba(0,122,255,0.15);">🔒</div>
        <span class="ios-row-label" style="font-size:14px;color:var(--text2);line-height:1.5;">Veriler sunucuda saklanmaz, yerel havuzda tutulur.</span>
      </div>
    </div>
  </div>

</div>
<div id="ios-toast"></div>
</body>
</html>