<?php
session_start();
require_once 'db.php';
require_once 'QueueManager.php';  // ← ДОБАВИТЬ

// Получаем данные из формы
$name = $_POST['name'] ?? '';
$tour_date = $_POST['date'] ?? '';
$route = $_POST['route'] ?? '';
$audio_guide = isset($_POST['audio_guide']) ? 'yes' : 'no';
$language = $_POST['language'] ?? '';

// Очищаем данные
$name = htmlspecialchars(trim($name));
$tour_date = htmlspecialchars(trim($tour_date));
$route = htmlspecialchars(trim($route));
$language = htmlspecialchars(trim($language));

// Преобразуем технические значения в читаемые
$route_display = [
    "historic" => "Рыбная деревня",
    "museum" => "Амалиенау", 
    "parks" => "Подземелья и оборонительные валы",
    "architecture" => "Куршская коса"
][$route] ?? $route;

$language_display = [
    "russian" => "Русский",
    "english" => "Английский", 
    "german" => "Немецкий"
][$language] ?? $language;

// Валидация
$errors = [];
if(empty($name)) $errors[] = "Имя обязательно";
if(empty($tour_date)) $errors[] = "Дата обязательна";
if(empty($route)) $errors[] = "Выберите маршрут";
if(empty($language)) $errors[] = "Выберите язык экскурсии";

if(!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: index.php");
    exit();
}

//  АСИНХРОННАЯ ОБРАБОТКА - отправляем в очередь
try {
    $queueManager = new QueueManager();
    
    $queueData = [
        'type' => 'excursion_booking',
        'name' => $name,
        'tour_date' => $tour_date,
        'route' => $route_display,
        'audio_guide' => $audio_guide,
        'language' => $language_display,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    $queueManager->publishToMain($queueData);  // ← ИЗМЕНИЛИ на publishToMain
    
    // Сохраняем в сессию для немедленного показа
    $_SESSION['last_booking'] = [
        'name_display' => $name,
        'date_display' => date('d.m.Y', strtotime($tour_date)),
        'route_display' => $route_display,
        'audio_guide_display' => $audio_guide === 'yes' ? 'Да (платно)' : 'Нет',
        'language_display' => $language_display
    ];
    
    $_SESSION['success'] = " Запись принята! Обрабатывается асинхронно через RabbitMQ";
    
} catch (Exception $e) {
    $_SESSION['errors'] = [" Ошибка отправки в очередь: " . $e->getMessage()];
}

header("Location: index.php");
exit();
?>