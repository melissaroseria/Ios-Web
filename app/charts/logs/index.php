<?php
// Log dosyalarını al
$logFiles = glob('logs/*.log');

// Log kaydını yapma
$logFile = 'logs/' . date('Y-m-d') . '.log';
$logMessage = "[" . date('Y-m-d H:i:s') . "] IP: {$_SERVER['REMOTE_ADDR']} | Sayfa yenilendi\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Sayfası</title>
    <link rel="stylesheet" href="../../../css/boot-log.css">
</head>
<body>
    <!-- Üst Kısım (Navbar) -->
    <div class="header">
        <div class="profile">
            <img src="../../run/assets/user/plus/users.png" alt="Profil Resmi" class="profile-pic">
            <h1>Log Sayfası</h1>
        </div>
    </div>

    <!-- Ana Konteyner (Liste) -->
    <div class="container">
        <div class="log-files">
            <h2>Log Dosyaları</h2>
            <div class="log-list">
                <?php foreach($logFiles as $file): ?>
                    <div class="log-item" data-file="<?php echo basename($file); ?>" onclick="openModal('<?php echo basename($file); ?>')">
                        <span class="emoji">📒</span>
                        <p class="file-name"><?php echo basename($file); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal (Log İçeriği) -->
    <div id="logModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">❌</span>
            <h2>Log İçeriği</h2>
            <pre id="logContent"></pre>
        </div>
    </div>

    <!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <p>&copy; 2025 Tüm Hakları Saklıdır.</p>
        <p>Powered by <span class="ai-text">AI</span></p>
    </div>
</footer>
    <!-- JavaScript -->
    <script src="app.js"></script>
</body>
</html>