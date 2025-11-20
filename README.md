# Лабораторная работа №6: Нереляционные базы данных

## Автор
**ФИО:** Веремьева Елизавета Марковна  
**Группа:** 3МО-1  
**Вариант:** 8 - Новости (Elasticsearch)

## Описание задания
Изучение нереляционных баз данных (Redis, Elasticsearch, ClickHouse) и взаимодействие с ними через API с помощью GuzzleClient.

## Цели работы
- Настроить Redis, Elasticsearch, ClickHouse в Docker
- Освоить работу с NoSQL базами через HTTP API
- Реализовать взаимодействие с Elasticsearch для работы с новостями
- Закрепить навыки работы с Guzzle HTTP клиентом

## Технологии
- PHP 8.2 + GuzzleHTTP
- Elasticsearch 8.10.2
- Redis 7
- ClickHouse 24
- Docker

## Запуск проекта
```bash
docker-compose up -d --build
```
Открыть: http://localhost:8080

## Результат
✅ Реализовано взаимодействие с тремя NoSQL базами через HTTP API
✅ Созданы классы для работы с Elasticsearch, Redis, ClickHouse
✅ Адаптировано под вариант 8 - работа с новостями в Elasticsearch
✅ Настроена Docker-инфраструктура для всех СУБД