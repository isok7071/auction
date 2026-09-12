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

## Импорт исходных данных

Полный импорт выполняется по шагам:

1. Создать каталог для исходного Git-репозитория:

```bash
mkdir -p storage/app
```

2. Клонировать репозиторий с исходными данными:

```bash
git clone https://gitlab.fdw.ru/ext/test_task storage/app/import-source
```

3. Запустить приложение и MySQL:

```bash
docker compose up -d nginx
```

4. Применить миграции, если ранее не применены:

```bash
docker compose exec app php artisan migrate
```

5. Создать публичную ссылку Laravel Storage:

```bash
docker compose exec app php artisan storage:link
```

6. Запустить импорт:

```bash
docker compose exec app php artisan cars:import storage/app/import-source
```

Команда импортирует `AuctionItemId`, основные поля `Make`, `Model`, `Year` и другие нужные поля в MySQL, а фото сохраняет на public disk по пути `cars/{Image}`. Повторный запуск обновляет записи по `AuctionItemId`/имени фото и не создаёт дубликаты. Каталог `storage/app/import-source` не коммитится.

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

```text
app/
├── Console/Commands/          # Artisan-команды: импорт исходных данных
├── Domain/
│   ├── Cars/                  # Car, CarPhoto, import contracts и DTO
│   ├── Voting/                # Vote, pair/vote contracts и DTO
│   └── Statistics/            # statistics contracts и DTO
├── Infrastructure/
│   ├── Persistence/Eloquent/  # реализации repository contracts через Eloquent
│   └── Storage/               # адаптер Laravel public filesystem
├── Http/                      # thin controllers, Form Requests, Resources
├── Services/                  # CarImportService, VotingPairService,
│                              # VoteService, StatisticsService
└── Providers/                 # DI bindings Domain -> Infrastructure

database/
├── migrations/                # cars, car_photos, votes и стандартные Laravel tables
├── factories/                 # тестовые состояния доменных моделей
└── seeders/                   # стандартная точка Laravel для seed-данных

resources/
├── views/                     # Blade-страницы voting и statistics
├── js/                        # отдельные Vite entrypoints: jQuery и Vue
└── css/                       # общие и page-specific стили

routes/
├── web.php                    # страницы и session/CSRF-защищённые JSON endpoints
└── console.php                # консольная регистрация Laravel-команд

tests/
├── Feature/Database/          # миграции, модели и связи
├── Feature/Console/           # импорт JSON и фотографий
├── Feature/Http/              # endpoints, validation и Resources
├── Unit/Services/             # pair cycle, votes и statistics
└── Fixtures/                  # маленькие локальные import fixtures

docker/                        # PHP-FPM image и nginx config
public/build/                  # production Vite assets
storage/app/import-source/     # локальный источник импорта, не коммитится
```

Backend boundaries:

- `Domain` содержит модели, контракты и immutable DTO; бизнес-правила не зависят от Eloquent query деталей.
- `Infrastructure` содержит Eloquent repositories и файловое хранилище; реализации подключаются через `AppServiceProvider`.
- `Services` координируют сценарии импорта, голосования и статистики.
- `Http` остаётся тонким: Form Request → service → API Resource/JSON.

Frontend boundaries соответствуют заданию: voting page использует только jQuery/AJAX и ezPlus Tints; statistics page использует только Vue.js. Эти области не смешиваются.

Планируемые backend endpoints следующих этапов: `GET /voting/models`, `GET /voting/pair`, `POST /voting/votes`, `GET /statistics`. Точные поля контрактов описаны в backend-плане [`.agents/plans/fordewind-laravel-design.md`](.agents/plans/fordewind-laravel-design.md).

## Документация

| Файл | Что внутри |
| --- | --- |
| `Task.md` | исходное задание |
| `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md` | продукт и бизнес-логика |
| `docs/agents` | техническая документация процесса разработки |
