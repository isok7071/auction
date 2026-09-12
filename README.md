# Fordewind

Laravel-приложение для попарного голосования за фотографии аукционных автомобилей и просмотра статистики по модели и году выпуска.

## Требования

- Docker Engine с Docker Compose v2;
- Git для загрузки исходных данных;
- свободные порты `8080`, `3306` и `5173`.

## Запуск с нуля

Все команды выполняются из корня репозитория в указанном порядке.

1. Создайте локальную конфигурацию:
  ```bash
   cp .env.example .env
  ```
2. Соберите PHP-образ и установите зависимости. `composer install` должен быть выполнен до первого вызова `php artisan`:
  ```bash
   docker compose build app

   docker compose run --rm --no-deps --user "$(id -u):$(id -g)" app composer install

   docker compose run --rm --no-deps --user "$(id -u):$(id -g)" app php artisan key:generate

   docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm ci
  ```
3. Поднимите PHP-FPM, nginx и MySQL:
  ```bash
   docker compose up -d nginx
  ```
4. Примените миграции и создайте ссылку на публичное хранилище:
  ```bash
   docker compose exec app php artisan migrate
   docker compose exec app php artisan storage:link
  ```



## Доступные адреса


| Адрес                              | Назначение                                          |
| ---------------------------------- | --------------------------------------------------- |
| `http://localhost:8080/`           | Попарное голосование на jQuery/AJAX.                |
| `http://localhost:8080/statistics` | Статистика на Vue 3.                                |
| `http://localhost:5173`            | Vite dev server и HMR во время frontend-разработки. |
| `localhost:3306`                   | MySQL с хоста; из Docker используется `mysql:3306`. |


Корневой `.env` используют Laravel и Vite. Значения MySQL по умолчанию: база `fordewind`, пользователь `fordewind`, пароль `fordewind`.

## Импорт данных

1. Загрузите исходные JSON и фотографии в локальный каталог:
  ```bash
   mkdir -p storage/app
   git clone https://gitlab.fdw.ru/ext/test_task storage/app/import-source
  ```
2. После запуска контейнеров выполните команду импорта:
  ```bash
   docker compose exec app php artisan cars:import storage/app/import-source
  ```

Команда импортирует автомобили и копирует фотографии в public storage (`cars/{Image}`). Её можно безопасно запускать повторно: записи обновляются по `AuctionItemId`, фотографии — по имени исходного файла, без дубликатов. `storage/app/import-source` не коммитится.

## Frontend

Для разработки с Vite и HMR в отдельном терминале:

```bash
docker compose --profile assets up node
```

Открывайте Laravel через `http://localhost:8080`, а Vite автоматически подхватит изменения. Для production-сборки вместо dev server:

```bash
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
```

## Структура проекта

Тут я немного переусложнил, можно было сделать проще без доменов, а просто controller, service, repository, но хотелось сделать красиво

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
├── js/
│   ├── voting.js               # jQuery/AJAX и ezPlus только для голосования
│   └── statistics/             # Vue SFC: api, composables и components
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
docker-compose.yml             # app, nginx, mysql и profile assets/node
public/build/                  # production Vite assets
storage/app/import-source/     # локальный источник импорта, не коммитится
docs/agents/                   # планы, решения, журнал AI-workflow и handoff
```

Backend boundaries:

- `Domain` содержит модели, контракты и immutable DTO; бизнес-правила не зависят от Eloquent query деталей.
- `Infrastructure` содержит Eloquent repositories и файловое хранилище; реализации подключаются через `AppServiceProvider`.
- `Services` координируют сценарии импорта, голосования и статистики.
- `Http` остаётся тонким: Form Request → service → API Resource/JSON.

Frontend: страница голосованния использует только jQuery/AJAX и ezPlus Tints; Страница статистики использует только Vue.js. 

## Пользовательские страницы

- `/` — адаптивное попарное голосование на jQuery/AJAX с увеличением фотографий через ezPlus Tints.
- `/statistics` — Vue 3 Composition API приложение с фильтрами по модели и диапазону годов, desktop-таблицей и mobile-карточками.

Vue-часть построена из однофайловых компонентов в `resources/js/statistics`: запросы находятся в `api`, реактивное состояние — в `composables`, интерфейс — в `components`. Голосование и статистика имеют отдельные Vite entrypoints и не смешивают jQuery с Vue.

## API

Frontend использует следующие JSON endpoints:


| Метод                                                                   | Назначение                                                                    | Успешный ответ                                                                                            |
| ----------------------------------------------------------------------- | ----------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------- |
| `GET /voting/models`                                                    | Список доступных моделей с минимум двумя фото                                 | `data: [{key, label}]`                                                                                    |
| `GET /voting/pair?model=FORD%20MUSTANG`                                 | Выдать или повторно вернуть незавершённую пару текущей сессии                 | `data: {model, left: {id, car_id, url}, right: {…}}`                                                      |
| `POST /voting/votes`                                                    | Сохранить голос по выданной паре                                              | `201 data: {id, winner_photo_id, loser_photo_id}`                                                         |
| `GET /statistics/models`                                                | Полный список моделей для selector-фильтра статистики                         | `data: [{key, label}]`                                                                                    |
| `GET /statistics/data?model=…&year_from=…&year_to=…&page=1&per_page=24` | Постраничный список автомобилей и голосов по фильтрам; `per_page` от 1 до 100 | `data: [{car fields, photo, votes_count}], meta: {total_votes, current_page, last_page, per_page, total}` |


Если модель не найдена или параметры запроса неверные, сервер отвечает ошибкой валидации (код 422). Если модель есть, но фотографий меньше двух, пара не выдаётся: страница должна показать пустое состояние.

Голос можно отдать только за ту пару, которую сервер уже показал в этом браузере. Пока голос не сохранён, повторный запрос той же модели вернёт ту же пару, а не новую. Если пользователь быстро нажмёт дважды, второй запрос не выдаст другую пару и не запишет голос повторно.

## Документация


| Файл                                                            | Что внутри                                                              |
| --------------------------------------------------------------- | ----------------------------------------------------------------------- |
| `Task.md`                                                       | исходное задание которое я использовал для декомпоза и написания планов |
| `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md` | продукт и бизнес-логика                                                 |
| `docs/agents`                                                   | техническая документация процесса разработки                            |




## Использование AI-инструментов

При разработке использовался Cursor и Codex как AI-ассистент. Артефакты процесса сохранены в `docs/agents`, `.agents/plans` и `docs/superpowers`.

- Для анализа требований и декомпозиции использовались skills `brainstorming`, `writing-plans`, `executing-plans` и проектные правила Fordewind/Laravel.
- Для реализации и исправлений применялись TDD, `systematic-debugging`, PHPUnit, Composer audit и Vite build.
- Для проверки интерфейса использовались Browser MCP и временный Chromium runtime Playwright; API и Laravel-контракты проверялись через Docker runtime.
- Код прошёл независимые read-only ревью backend и frontend; результаты, решения и команды верификации зафиксированы в `docs/agents/AI_WORKFLOW.md` и `docs/agents/DECISIONS.md`.
- Каждую подзадачу я реализовывал по отдельным сессиям, чтобы не смешивать контекст агента.

Краткое резюме работы:
Cначала были подготовлен скелет проекта: harness, skills, .agents, ТЗ преобразовано в .md формат -> написано техническое тз -> декомпозировано на задачи для агента.
Далее были подготовлены Laravel, Docker Compose файлы и импорт; затем реализованы серверные сценарии голосования и статистики; после этого frontend разделён на jQuery-область голосования и Vue 3 Composition api статистики. Добавлены серверная пагинация статистики, selector моделей и годов, адаптивный ezPlus, обработка ошибок загрузки фото, rate limit голосов, ревью кода и воспроизводимый README. 
Решения по финальному ревью кода (после агента), объёму, архитектуре, плану, приоритетам подтверждал я, результаты фиксировались командами тестов и сборки.