// После существующего кода в worker.php ДОБАВЬ:
$queueManager->consume(function($data) use ($elastic) {
    echo " Получено сообщение: " . json_encode($data) . "\n";
    
    try {
        // Обрабатываем разные типы сообщений
        if (isset($data['type']) && $data['type'] === 'excursion_booking') {
            // Обработка записи на экскурсию
            echo " Обработка записи на экскурсию...\n";
            
            // Сохраняем в лог
            file_put_contents('processed_excursions.log', 
                date('Y-m-d H:i:s') . ' - EXCURSION: ' . json_encode($data) . PHP_EOL, 
                FILE_APPEND
            );
            
            echo " Запись на экскурсию обработана\n";
            
        } elseif (isset($data['title'])) {
            // Обработка новости (существующий код)
            echo " Обработка новости...\n";
            $result = $elastic->addNews(
                uniqid(),
                $data['title'],
                $data['content'],
                $data['category'] ?? 'general',
                $data['date'] ?? date('Y-m-d')
            );
            echo " Новость сохранена в Elasticsearch\n";
            file_put_contents('processed_news.log', 
                date('Y-m-d H:i:s') . ' - NEWS: ' . json_encode($data) . PHP_EOL, 
                FILE_APPEND
            );
        }
        
        sleep(1); // Имитация обработки
        echo " Сообщение обработано успешно\n";
        
    } catch (Exception $e) {
        echo " Ошибка обработки: " . $e->getMessage() . "\n";
        file_put_contents('error.log', 
            date('Y-m-d H:i:s') . ' - ERROR: ' . $e->getMessage() . ' - ' . json_encode($data) . PHP_EOL, 
            FILE_APPEND
        );
    }
    
    echo "---\n";
});