<?php
require_once 'QueueManager.php';
require_once 'ElasticExample.php';
require_once 'ClientFactory.php';

echo " Worker для обработки новостей запущен...\n";
echo " Ожидание сообщений из RabbitMQ...\n";

$queueManager = new QueueManager();
$elastic = new ElasticExample();

$queueManager->consume(function($data) use ($elastic) {
    echo " Получено сообщение: " . json_encode($data) . "\n";
    
    try {
        // Обрабатываем данные - сохраняем в Elasticsearch
        if (isset($data['title']) && isset($data['content'])) {
            $result = $elastic->addNews(
                uniqid(), // генерируем уникальный ID
                $data['title'],
                $data['content'],
                $data['category'] ?? 'general',
                $data['date'] ?? date('Y-m-d')
            );
            
            echo " Новость сохранена в Elasticsearch\n";
            file_put_contents('processed_news.log', 
                date('Y-m-d H:i:s') . ' - ' . json_encode($data) . PHP_EOL, 
                FILE_APPEND
            );
        }
        
        // Имитируем обработку
        sleep(1);
        echo " Сообщение обработано успешно\n";
        
    } catch (Exception $e) {
        echo " Ошибка обработки: " . $e->getMessage() . "\n";
        file_put_contents('error_news.log', 
            date('Y-m-d H:i:s') . ' - ERROR: ' . $e->getMessage() . ' - ' . json_encode($data) . PHP_EOL, 
            FILE_APPEND
        );
    }
    
    echo "---\n";
});