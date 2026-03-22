<?php
// 1. Dizin Kontrolü: Notların kaydedileceği klasör yoksa oluştur.
$noteDir = 'notes/';
if (!is_dir($noteDir)) {
    mkdir($noteDir, 0777, true);
}

// 2. Veri Kontrolü: Sadece POST isteği geldiğinde çalış.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Formdan gelen verileri temizleyerek al
    $title = isset($_POST['title']) ? trim(htmlspecialchars($_POST['title'])) : '';
    $note = isset($_POST['note']) ? htmlspecialchars($_POST['note']) : '';

    // Eğer başlık boşsa, tarih ve saati başlık yap (iOS stili)
    if (empty($title)) {
        $title = "Not " . date('d-m-Y H-i');
    }

    // 3. Kayıt İşlemi: Başlık.txt olarak içeriği kaydet
    $fileName = $noteDir . $title . '.txt';
    
    // file_put_contents ile dosyayı oluştur veya üzerine yaz
    if (file_put_contents($fileName, $note) !== false) {
        // Başarılıysa ana listeye (index.php) geri dön
        header('Location: index.php');
        exit;
    } else {
        // Bir hata oluşursa ekrana bas (Dizin izinlerini kontrol et)
        echo "Hata: Not kaydedilemedi. Lütfen 'notes' klasörünün yazma izinlerini kontrol et kanki.";
    }
} else {
    // Eğer bu dosyaya direkt girilmeye çalışılırsa ana sayfaya postala
    header('Location: index.php');
    exit;
}
?>
