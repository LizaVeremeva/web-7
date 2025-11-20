<?php
require_once 'QueueManager.php';
require_once 'ElasticExample.php';
require_once 'ClientFactory.php';
require_once 'db.php';

echo " Worker для обработки экскурсий запущен...\n";
echo " Ожидание сообщений из RabbitMQ...\n";
echo " Две очереди: excursions_queue (основная) и errors_queue (ошибки)\n";
echo "---\n";

$queueManager = new QueueManager();

$queueManager->consumeMain(function($data) {
    echo " Получено сообщение из основной очереди: " . json_encode($data) . "\n";
    
    try {
        // Проверяем тип сообщения
        if (isset($data['type']) && $data['type'] === 'excursion_booking') {
            echo " Обработка записи на экскурсию...\n";
            
            // Имитируем возможную ошибку (для тестирования)
            if (rand(1, 10) === 1) { // 10% chance of error
                throw new Exception("Случайная ошибка при обработке экскурсии");
            }
            
            // Сохраняем в базу данных (основная обработка)
            $pdo = new PDO(
                "mysql:host=mysql;dbname=lab5_db", 
                "lab5_user", 
                "lab5_password"
            );
            
            $sql = "INSERT INTO excursions (name, excursion_date, route, audio_guide, language) 
                    VALUES (:name, :excursion_date, :route, :audio_guide, :language)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':excursion_date' => $data['tour_date'],
                ':route' => $data['route'],
                ':audio_guide' => $data['audio_guide'],
                ':language' => $data['language']
            ]);
            
            $excursionId = $pdo->lastInsertId();
            
            // Логируем успешную обработку
            file_put_contents('processed_excursions.log', 
                date('Y-m-d H:i:s') . ' - SUCCESS - ID: ' . $excursionId . ' - ' . 
                json_encode($data) . PHP_EOL, 
                FILE_APPEND
            );
            
            echo " Запись на экскурсию успешно сохранена в БД. ID: " . $excursionId . "\n";
            
        } else {
            throw new Exception("Неизвестный тип сообщения: " . ($data['type'] ?? 'отсутствует'));
        }
        
        // Имитация обработки
        sleep(1);
        
    } catch (Exception $e) {
        // Пробрасываем ошибку для обработки в QueueManager
        throw new Exception("Ошибка обработки экскурсии: " . $e->getMessage());
    }
    
    echo "---\n";
});