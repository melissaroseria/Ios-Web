<?php
// process.php

if (isset($_POST['apiUrl'])) {
    $apiUrl = $_POST['apiUrl'];
    
    // API isteğini gönder
    $response = file_get_contents($apiUrl);
    
    if ($response === FALSE) {
        echo "API'den veri alınamadı.";
    } else {
        // JSON yanıtı PHP'ye dönüştür
        $data = json_decode($response, true);
        
        // JSON verisini döndür
        echo json_encode($data);
    }
}
?>
