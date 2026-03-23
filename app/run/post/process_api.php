<?php
// POST ile gelen JSON verisini alıyoruz
$input = file_get_contents('php://input');
$data = json_decode($input, true); // JSON'u PHP dizisine çevir

// Veriyi işleyin veya başka işlemler yapın (örneğin veritabanına kaydedebilirsiniz)
if ($data) {
    // Burada işleme yapabilirsiniz (örneğin veriyi düzenleme)
    // Örnek olarak sadece gelen veriyi döndüreceğiz
    $response = [
        'status' => 'success',
        'receivedData' => $data
    ];
} else {
    // Eğer veri geçerli değilse hata döndürüyoruz
    $response = [
        'status' => 'error',
        'message' => 'Geçersiz veri alındı.'
    ];
}

// JSON formatında geri döndürüyoruz
header('Content-Type: application/json');
echo json_encode($response);
?>
