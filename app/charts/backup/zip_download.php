<?php
// Klasörleri tanımla
$folders = [
    '../../../src',
    '../../run/notes/notes',
    '../../run/gallery/uploads',
    '../../run/sudo/uploads'
];

// Zip dosyasının adı
$zipFile = 'ravios.zip';

// Zip dosyasını oluştur
$zip = new ZipArchive();

// Dosya açma
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    exit("Zip dosyası oluşturulamadı.");
}

// Klasörleri zip dosyasına ekle
foreach ($folders as $folder) {
    if (is_dir($folder)) {
        // Dosya ve klasörleri eklemek için yine RecursiveIteratorIterator kullanıyoruz
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        // Klasör yapısını kaydetmek için addEmptyDir kullanabiliriz
        $relativeFolder = basename($folder);
        $zip->addEmptyDir($relativeFolder);

        // Dosyaları zip dosyasına ekle
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $relativeFolder . DIRECTORY_SEPARATOR . substr($filePath, strlen(realpath($folder)) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    } else {
        echo "Klasör bulunamadı: " . $folder;
    }
}

// Zip dosyasını kapat
$zip->close();

// Dosya indirme için çıktı ver
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFile . '"');
header('Content-Length: ' . filesize($zipFile));

readfile($zipFile);

// Geçici zip dosyasını sil
unlink($zipFile);
?>