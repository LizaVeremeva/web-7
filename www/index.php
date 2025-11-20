<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная - Экскурсии Калининград + RabbitMQ</title>
    <style>
        .booking-card {
            border: 2px solid #4CAF50;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            background: #f9fff9;
        }
        .booking-card h3 {
            color: #2E7D32;
            margin-top: 0;
        }
        .error-box {
            color: red; 
            background: #ffe6e6; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid red;
        }
        .nav-button {
            padding: 10px 15px; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            margin: 15px 0;
        }
        .stat-box {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .main-queue {
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
        }
        .error-queue {
            background: #ffebee;
            border-left: 4px solid #F44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Добро пожаловать на сайт экскурсий по Калининграду!</h1>
        <h2>Лабораторная работа 7: Асинхронная обработка через RabbitMQ</h2>
        
        <!-- Блок для вывода ошибок -->
        <?php if(isset($_SESSION['errors'])): ?>
            <div class="error-box">
                <strong>Ошибки при записи:</strong>
                <ul>
                    <?php foreach($_SESSION['errors'] as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        
        <!-- Блок для вывода успешного сообщения -->
        <?php if(isset($_SESSION['success'])): ?>
            <div style="color: green; background: #e8f5e8; padding: 10px; margin: 10px 0; border: 1px solid green; border-radius: 5px;">
                ✅ <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <!-- Вывод последней брони из сессии -->
        <?php if(isset($_SESSION['last_booking'])): ?>
            <div class="booking-card">
                <h3>Ваша запись принята!</h3>
                <p><strong>Имя:</strong> <?= $_SESSION['last_booking']['name_display'] ?></p>
                <p><strong>Дата экскурсии:</strong> <?= $_SESSION['last_booking']['date_display'] ?></p>
                <p><strong>Маршрут:</strong> <?= $_SESSION['last_booking']['route_display'] ?></p>
                <p><strong>Аудиогид:</strong> <?= $_SESSION['last_booking']['audio_guide_display'] ?></p>
                <p><strong>Язык экскурсии:</strong> <?= $_SESSION['last_booking']['language_display'] ?></p>
            </div>
        <?php endif; ?>

        <!-- Статистика очередей RabbitMQ -->
        <div style="margin: 20px 0; padding: 15px; background: #fff3e0; border-radius: 5px; border-left: 4px solid #FF9800;">
            <h3 style="margin-top: 0; color: #EF6C00;">📊 Статистика очередей RabbitMQ</h3>
            <?php
            try {
                require_once 'QueueManager.php';
                $queueManager = new QueueManager();
                $stats = $queueManager->getQueueStats();
                
                echo "<div class='stats-container'>";
                echo "<div class='stat-box main-queue'>";
                echo "<h4 style='margin: 0 0 10px 0; color: #2E7D32;'>📨 Основная очередь</h4>";
                echo "<p style='font-size: 24px; margin: 0; color: #2E7D32;'><strong>{$stats['main']}</strong></p>";
                echo "<small>Сообщений в обработке</small>";
                echo "</div>";
                
                echo "<div class='stat-box error-queue'>";
                echo "<h4 style='margin: 0 0 10px 0; color: #C62828;'>⚠️ Очередь ошибок</h4>";
                echo "<p style='font-size: 24px; margin: 0; color: #C62828;'><strong>{$stats['error']}</strong></p>";
                echo "<small>Сообщений с ошибками</small>";
                echo "</div>";
                echo "</div>";
                
                echo "<p style='margin-top: 15px; font-size: 14px; color: #666;'>";
                echo "💡 Сообщения обрабатываются асинхронно. При ошибке автоматически перемещаются в очередь ошибок.";
                echo "</p>";
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Не удалось получить статистику: " . $e->getMessage() . "</p>";
            }
            ?>
        </div>

        <!-- Навигация -->
        <nav style="margin: 30px 0; text-align: center;">
            <a href="form.html" class="nav-button" style="background: #4CAF50;">
                Записаться на экскурсию
            </a> 
            <a href="view.php" class="nav-button" style="background: #2196F3;">
                Посмотреть все записи
            </a>
            <a href="rabbit_test.php" class="nav-button" style="background: #FF9800;">
                🧪 Тест очередей
            </a>
            <a href="errors.php" class="nav-button" style="background: #F44336;">
                ⚠️ Ошибки
            </a>
        </nav>

        <!-- Информация о экскурсиях -->
        <div style="margin-top: 40px;">
            <h2>Наши экскурсии:</h2>
            <ul>
                <li><strong>Рыбная деревня</strong> - исторический центр города</li>
                <li><strong>Амалиенау</strong> - район немецких вилл</li>
                <li><strong>Подземелья и оборонительные валы</strong> - военная история</li>
                <li><strong>Куршская коса</strong> - уникальный природный заповедник</li>
            </ul>
        </div>

        <!-- Блок с достопримечательностями из API -->
        <?php if(isset($_SESSION['api_data'])): ?>
            <div style="margin-top: 40px; border: 2px solid #FF9800; border-radius: 10px; padding: 20px; background: #fffaf0;">
                <h2 style="color: #FF9800;">🏛️ Достопримечательности Калининграда:</h2>
                <?php 
                if(isset($_SESSION['api_data']['error'])) {
                    echo "<p style='color: red;'>Ошибка загрузки достопримечательностей: " . htmlspecialchars($_SESSION['api_data']['error']) . "</p>";
                } elseif(isset($_SESSION['api_data']['features']) && !empty($_SESSION['api_data']['features'])) {
                    $attractions = array_slice($_SESSION['api_data']['features'], 0, 5);
                    foreach($attractions as $attraction): 
                        $name = $attraction['properties']['name'] ?? 'Без названия';
                        $kinds = $attraction['properties']['kinds'] ?? '';
                ?>
                    <div style="margin-bottom: 15px; padding: 10px; background: white; border-radius: 5px; border-left: 4px solid #FF9800;">
                        <strong>📍 <?= htmlspecialchars($name) ?></strong><br>
                        <small>🏷️ <?= htmlspecialchars(str_replace(',', ', ', $kinds)) ?></small>
                    </div>
                <?php 
                    endforeach; 
                    echo "<p><small>Данные предоставлены OpenTripMap API</small></p>";
                } else {
                    echo "<p>Достопримечательности не найдены</p>";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Вывод всех записей из базы данных -->
        <?php
        require_once 'db.php';

        // Получаем все записи из БД
        try {
            $stmt = $pdo->query("SELECT * FROM excursions ORDER BY created_at DESC");
            $all_excursions = $stmt->fetchAll();
        } catch(PDOException $e) {
            $all_excursions = [];
            echo "<p style='color: red;'>Ошибка загрузки данных: " . $e->getMessage() . "</p>";
        }
        ?>

        <div style="margin-top: 40px;">
            <h2>📋 Все записи на экскурсии из базы данных:</h2>
            
            <?php if(!empty($all_excursions)): ?>
                <div style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #f9f9f9;">
                    <?php foreach($all_excursions as $row): ?>
                        <div style="padding: 10px; border-bottom: 1px solid #eee;">
                            <strong>👤 <?= htmlspecialchars($row['name']) ?></strong><br>
                            📅 Дата: <?= $row['excursion_date'] ?> | 
                            🗺️ Маршрут: <?= htmlspecialchars($row['route']) ?> | 
                            🎧 Аудиогид: <?= $row['audio_guide'] === 'yes' ? 'Да' : 'Нет' ?> | 
                            🗣️ Язык: <?= htmlspecialchars($row['language']) ?><br>
                            <small>🆔 ID: <?= $row['id'] ?> | 📅 Создано: <?= $row['created_at'] ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Записей на экскурсии пока нет. Заполните форму чтобы добавить первую запись!</p>
            <?php endif; ?>
        </div>

        <!-- Текущее время -->
        <p style="margin-top: 30px; color: #666;">Текущее время: <span id="time"></span></p>
    </div>

    <script>
        document.getElementById('time').textContent = new Date().toLocaleTimeString();
    </script>
</body>
</html>