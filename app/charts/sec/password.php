<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'];
    file_put_contents("../../../src/pass.txt", $newPassword);
    header("Location: index.php");  // Kurulum tamamlanınca giriş ekranına yönlendirme
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Belirleme</title>
    <link rel="stylesheet" href="../../../css/boot-change.css">
</head>
<body class="background">
    <div class="container">
        <div class="header">
            <h2 class="setup-title">Şifre Belirleyin</h2>
        </div>

        <div class="password-box">
            <form method="POST" action="">
                <label for="password">Yeni Şifre:</label>
                <input type="password" id="password" name="password" placeholder="Yeni şifre girin" required>
                
                <button type="submit" class="login-button">Şifreyi Kaydet</button>
            </form>
        </div>
    </div>
</body>
</html>