<?php
require 'vendor/autoload.php';
require 'ClientFactory.php';
require 'ElasticExample.php';
require 'RedisExample.php';
require 'ClickhouseExample.php';

echo "<h1>Лабораторная работа 6 - Нереляционные базы данных</h1>";
echo "<h2>Вариант 8: Новости</h2>";

try {
    // Elasticsearch - Новости
    echo "<h3> Elasticsearch - Работа с новостями:</h3>";
    $elastic = new ElasticExample();
    
    $result1 = $elastic->addNews(1, 'Новая технология в IT', 'Компания представила инновационную разработку...', 'технологии', '2024-01-15');
    echo "<pre>Добавлена новость: " . htmlspecialchars($result1) . "</pre>";
    
    $result2 = $elastic->searchNews(['title' => 'технология']);
    echo "<pre>Поиск новостей: " . htmlspecialchars($result2) . "</pre>";

    // Redis - Счетчики просмотров
    echo "<h3> Redis - Счетчики просмотров:</h3>";
    $redis = new RedisExample();
    
    $redis->setNewsCounter(1, 150);
    $views = $redis->getNewsCounter(1);
    echo "<p>Просмотры новости #1: " . ($views ?: '0') . "</p>";

    $redis->setValue('last_news_id', '100');
    $lastId = $redis->getValue('last_news_id');
    echo "<p>ID последней новости: " . ($lastId ?: 'не установлен') . "</p>";

    // ClickHouse - Аналитика
    echo "<h3> ClickHouse - Аналитика:</h3>";
    $click = new ClickhouseExample();
    
    $result3 = $click->query('SELECT version()');
    echo "<pre>Версия ClickHouse: " . htmlspecialchars($result3) . "</pre>";
    
    $result4 = $click->query('SELECT name FROM system.tables WHERE database = currentDatabase()');
    echo "<pre>Таблицы: " . htmlspecialchars($result4) . "</pre>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка: " . $e->getMessage() . "</p>";
    echo "<p><small>Подсказка: Elasticsearch может запускаться 1-2 минуты</small></p>";
}