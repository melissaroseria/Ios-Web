<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noteDir = 'notes/';
    $currentName = htmlspecialchars($_POST['currentName']);
    $newName = htmlspecialchars($_POST['newName']);
    
    // Dosya adını değiştir
    rename($noteDir . $currentName, $noteDir . $newName . '.txt');
    
    // Başarıyla eklendiğinde ana sayfaya yönlendir
    header('Location: index.php');
    exit;
}
