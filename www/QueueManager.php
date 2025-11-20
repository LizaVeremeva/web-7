<?php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueManager {
    private $channel;
    private $connection;
    private $mainQueue = 'excursions_queue';
    private $errorQueue = 'errors_queue';

    public function __construct() {
        $this->connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        
        // Объявляем две очереди
        $this->channel->queue_declare($this->mainQueue, false, true, false, false);
        $this->channel->queue_declare($this->errorQueue, false, true, false, false);
    }

    public function publishToMain($data) {
        $this->publish($data, $this->mainQueue);
        echo " Сообщение отправлено в основную очередь: " . json_encode($data) . "\n";
    }

    public function publishToError($data) {
        $this->publish($data, $this->errorQueue);
        echo " Сообщение отправлено в очередь ошибок: " . json_encode($data) . "\n";
    }

    private function publish($data, $queue) {
        $msg = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        $this->channel->basic_publish($msg, '', $queue);
    }

    public function getQueueStats() {
        try {
            $mainStats = $this->channel->queue_declare($this->mainQueue, true);
            $errorStats = $this->channel->queue_declare($this->errorQueue, true);
            
            return [
                'main' => $mainStats[1] ?? 0,    // количество сообщений в основной очереди
                'error' => $errorStats[1] ?? 0   // количество сообщений в очереди ошибок
            ];
        } catch (Exception $e) {
            return ['main' => 0, 'error' => 0];
        }
    }

    public function consumeMain(callable $callback) {
        echo " Обработчик основной очереди запущен...\n";

        $this->channel->basic_consume(
            $this->mainQueue, 
            '', 
            false, 
            false,  // ← Изменили на false для ручного подтверждения
            false, 
            false, 
            function($msg) use ($callback) {
                $data = json_decode($msg->body, true);
                
                try {
                    $callback($data);
                    $msg->ack(); // Подтверждаем успешную обработку
                    echo " Сообщение обработано успешно\n";
                } catch (Exception $e) {
                    // При ошибке отправляем в очередь ошибок
                    $this->publishToError([
                        'original_data' => $data,
                        'error' => $e->getMessage(),
                        'failed_at' => date('Y-m-d H:i:s')
                    ]);
                    $msg->ack(); // Все равно подтверждаем, чтобы убрать из основной очереди
                    echo " Ошибка обработки, сообщение перемещено в очередь ошибок\n";
                }
            }
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function __destruct() {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}