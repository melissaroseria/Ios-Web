<?php
// Ayarları okuma
$settings = json_decode(file_get_contents('charts/settings.json'), true);

// Kullanıcı bilgileri (Burayı User Summary'deki bilgilerine göre güncelleyebilirsin)
$user_profile_pic = '../app/run/assets/user/plus/users.png'; 
$user_name = 'Demo Üyelik'; 
$user_email = 'Apple Hesabı, iCloud ve fazlası'; 
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ayarlar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/boot-config.css">
</head>
<body>

<div class="settings-page">
    <div class="top-nav">
        <h1>Ayarlar</h1>
    </div>

    <div class="search-container">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <span>Ara</span>
            <i class="fas fa-microphone"></i>
        </div>
    </div>

    <div class="settings-group">
        <div class="profile-card" onclick="location.href='../index.php'">
            <img src="<?php echo htmlspecialchars($user_profile_pic); ?>" alt="Profil">
            <div class="profile-info">
                <span class="name"><?php echo htmlspecialchars($user_name); ?></span>
                <span class="subtext"><?php echo htmlspecialchars($user_email); ?></span>
            </div>
            <i class="fas fa-chevron-right arrow"></i>
        </div>
    </div>

    <div class="settings-group">
        <a href="charts/support/index.php" class="setting-item">
            <div class="icon-box" style="background: #007AFF;"><i class="fas fa-info-circle"></i></div>
            <span class="text">Hakkında</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
    </div>

    <div class="settings-group">
        <a href="charts/cache/index.php" class="setting-item">
            <div class="icon-box" style="background: #FF3B30;"><i class="fas fa-trash-alt"></i></div>
            <span class="text">Disk Yönetimi</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <a href="charts/sec/index.php" class="setting-item">
            <div class="icon-box" style="background: #34C759;"><i class="fas fa-shield-alt"></i></div>
            <span class="text">Güvenlik</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <a href="charts/logs/index.php" class="setting-item">
            <div class="icon-box" style="background: #5856D6;"><i class="fas fa-file-alt"></i></div>
            <span class="text">Log Kayıtları</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
        <a href="charts/screen/index.php" class="setting-item">
            <div class="icon-box" style="background: #AF52DE;"><i class="fas fa-sun"></i></div>
            <span class="text">Ekran ve Parlaklık</span>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
    </div>
</div>

</body>
</html>