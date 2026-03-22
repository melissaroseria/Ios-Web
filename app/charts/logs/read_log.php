<?php
// Dosya yolunu almak
if (isset($_GET['file'])) {
    $file = 'logs/' . $_GET['file'];

    // Dosya var mı kontrol et
    if (file_exists($file) && is_readable($file)) {
        echo file_get_contents($file);
    } else {
        echo "Dosya bulunamadı veya okunamıyor.";
    }
}
?>