# Fordewind

Laravel-приложение для попарного голосования за фотографии аукционных автомобилей и просмотра статистики по модели и году выпуска.

## Локальный запуск

Для запуска нужны Docker Engine с Docker Compose v2 и свободные порты `8080`, `3306` и `5173`.

Скопировать файл переменных окружения:

```bash
cp .env.example .env
```

Корневой `.env` используется Laravel и Vite-контейнером. В нём находятся настройки приложения, подключения к MySQL и Vite.

Для Docker Compose обязательны `DB_CONNECTION=mysql`, `DB_HOST=mysql`, `APP_URL=http://localhost:8080`, `SESSION_DRIVER=database` и `CACHE_STORE=database`.

Собрать PHP-образ:

```bash
docker compose build app
```

Сначала создать ключ приложения. Эта команда также подготовит Docker volumes для Laravel-кэша:

```bash
docker compose run --rm --no-deps app php artisan key:generate
```

Установить PHP-зависимости от имени текущего пользователя:

```bash
docker compose run --rm --no-deps --user "$(id -u):$(id -g)" app composer install
```

Установить frontend-зависимости:

```bash
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm install --ignore-scripts
```

Запустить приложение и MySQL:

```bash
docker compose up -d nginx
```

Приложение доступно по адресу `http://localhost:8080`. MySQL доступен из контейнеров по адресу `mysql:3306`, а с хоста — через порт `3306`.

Для разработки frontend запустить Vite:

```bash
docker compose --profile assets up node
```

Vite доступен напрямую на `http://localhost:5173`, HMR использует WebSocket на том же порту. Laravel продолжает открываться через `http://localhost:8080`, а браузер получает dev-ассеты напрямую от Vite.

Для production-сборки вместо dev-сервера выполнить:

```bash
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
```

После сборки nginx отдаёт файлы из `public/build` с долгим immutable-кешем. Остановить контейнеры можно командой:

```bash
docker compose stop
```

Значения MySQL по умолчанию из `.env.example`: база `fordewind`, пользователь `fordewind`, пароль `fordewind`. Для применения миграций после запуска приложения:

```bash
docker compose exec app php artisan migrate
```

## Тесты

```bash
docker compose run --rm app php artisan test --compact
```

Проверить compose-конфигурацию:

```bash
docker compose config
docker compose --profile assets config
```

## Структура проекта
//TODO пока будем меняться
- `app/` — backend Laravel.
- `resources/` — Blade, jQuery-страница голосования и Vue-страница статистики.
- `routes/` — web- и API-маршруты.
- `docker/` — PHP-FPM и nginx-конфигурация.
- `public/build/` — production-результат Vite.

## Документация

| Файл | Что внутри |
| --- | --- |
| `Task.md` | исходное задание |
| `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md` | продукт и бизнес-логика |
| `docs/agents` | техническая документация процесса разработки |
