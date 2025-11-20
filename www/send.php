<?php
require_once 'QueueManager.php';

// Получаем данные из POST или используем тестовые
$newsData = [
    'title' => $_POST['title'] ?? 'Тестовая новость ' . date('H:i:s'),
    'content' => $_POST['content'] ?? 'Содержание тестовой новости...',
    'category' => $_POST['category'] ?? 'technology',
    'date' => $_POST['date'] ?? date('Y-m-d'),
    'source' => 'web_form'
];

try {
    $queueManager = new QueueManager();
    $queueManager->publish($newsData);
    
    echo " Новость отправлена в очередь для асинхронной обработки!\n";
    echo " Данные: " . json_encode($newsData) . "\n";
    
} catch (Exception $e) {
    echo " Ошибка отправки: " . $e->getMessage() . "\n";
}