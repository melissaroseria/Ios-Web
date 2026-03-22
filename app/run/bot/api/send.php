<?php
header('Content-Type: application/json');

// Hataları bastıralım ki JSON bozulmasın
error_reporting(0);

$data = json_decode(file_get_contents('php://input'), true);
$token = ""; 

if(isset($data['chat_id']) && isset($data['text'])){
    $chatId = $data['chat_id'];
    $text = $data['text'];
    
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $params = [
        'chat_id' => $chatId,
        'text' => $text
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($params),
        ],
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        echo json_encode(['ok' => false, 'message' => 'Telegram API hatası']);
    } else {
        echo $result;
    }
} else {
    echo json_encode(['ok' => false, 'message' => 'Veri eksik']);
}
?>