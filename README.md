# Fordewind

Laravel-приложение для попарного голосования за фотографии аукционных автомобилей и просмотра статистики по модели и году выпуска.

## Требования

- Docker Engine с Docker Compose v2;
- Git для загрузки исходных данных;
- свободные порты `8080`, `3306` и `5173`.

## Запуск с нуля

Все команды выполняются из корня репозитория в указанном порядке.

1. Создайте локальную конфигурацию из [.env.example](.env.example):
  ```bash
   cp .env.example .env
  ```
  Корневой [.env](.env.example) используют Laravel и Vite. Значения MySQL по умолчанию: база `fordewind`, пользователь `fordewind`, пароль `fordewind`.
2. Соберите PHP-образ и установите зависимости. 
   ```bash
   docker compose build app

   # Подготовить named volumes для запуска Composer от имени пользователя хоста.
   docker compose run --rm --no-deps app true

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
6. Собрать Frontend в режиме prod сборки
  ```bash
    docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
  ```

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



## Доступные адреса


| Адрес                              | Назначение                                          |
| ---------------------------------- | --------------------------------------------------- |
| `http://localhost:8080/`           | Попарное голосование на jQuery/AJAX.                |
| `http://localhost:8080/statistics` | Статистика на Vue 3.                                |
| `http://localhost:5173`            | Vite dev server и HMR во время frontend-разработки. |
| `localhost:3306`                   | MySQL с хоста; из Docker используется `mysql:3306`. |




## Структура проекта

Выбрана структура Domain / Services / Infrastructure / HTTP, а не только Controller / Service / Repository, потому что приложение объединяет четыре разных сценария: импорт файлов, session-based выдачу пар, транзакционную запись голосов и агрегирующие запросы статистики. При текущем объёме данных хватило бы более простой схемы, но дополнительные слои не смешивают бизнес-правила, HTTP, Eloquent и storage.

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

`Services` координируют сценарии, `Domain` содержит модели, DTO и контракты, а `Infrastructure` реализует контракты через Eloquent и public storage. Контроллеры остаются тонкими: Form Request -> service -> Resource/JSON. Так получается больше файлов, но сценарии можно тестировать отдельно и менять способ хранения без переноса бизнес-логики.

Такой вариант выбран как основа для дальнейшего роста: бизнес-сценарии не зависят от конкретной реализации хранения, а каждую границу можно проверять отдельно.

Страница голосования использует только jQuery/AJAX и ezPlus Tints. Страница статистики использует только Vue.js.

## Пользовательские страницы

- `/` — адаптивное попарное голосование на jQuery/AJAX с увеличением фотографий через ezPlus Tints.
- `/statistics` — Vue 3 Composition API приложение с фильтрами по модели и диапазону годов, desktop-таблицей и mobile-карточками.

Vue-часть построена из однофайловых компонентов в [resources/js/statistics](resources/js/statistics): запросы находятся в `api`, реактивное состояние — в `composables`, интерфейс — в `components`. Голосование и статистика имеют отдельные Vite entrypoints и не смешивают jQuery с Vue.

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

## Что можно улучшить

На текущем объёме импорта существующих индексов достаточно. При существенном росте данных можно было бы рассмотреть следующие изменения:

- Добавить в `cars` generated column `model_key` со значением `CONCAT(make, ' ', model)` и индекс по `(model_key, year)`. Сейчас фильтр модели использует функцию в `WHERE`, поэтому составной индекс `(make, model, year)` для него не применяется.
- Добавить отдельный индекс `cars(year)`, если фильтрация только по диапазону лет станет частой и селективной. Текущий индекс начинается с `make`, поэтому такие запросы он не покрывает.



## Документация


| Файл                                                                                                                             | Что внутри                                                                 |
| -------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| [AGENTS.md](AGENTS.md)                                                                                                         | правила работы агентов, архитектурные ограничения и требования к проверкам |
| [Task.md](Task.md)                                                                                                             | исходное задание, использованное для декомпозиции и подготовки планов      |
| [docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md](docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md) | спецификация продукта и бизнес-логика                                      |
| [docs/superpowers/plans/2026-09-11-fordewind-implementation.md](docs/superpowers/plans/2026-09-11-fordewind-implementation.md) | пошаговый технический план реализации                                      |
| [.agents/plans/fordewind-laravel-design.md](.agents/plans/fordewind-laravel-design.md)                                         | план, подготовленный проектным harness                                     |
| [docs/agents/HANDOFF.md](docs/agents/HANDOFF.md)                                                                               | текущий этап, выполненные задачи, проверки и следующий шаг                 |
| [docs/agents/TOOLING.md](docs/agents/TOOLING.md)                                                                               | доступные инструменты и MCP-серверы                                        |
| [docs/agents/DECISIONS.md](docs/agents/DECISIONS.md)                                                                           | архитектурные и технические решения с обоснованием                         |
| [docs/agents/AI_WORKFLOW.md](docs/agents/AI_WORKFLOW.md)                                                                       | промпты, команды, результаты проверок и ограничения AI-assisted workflow   |
| [docs/agents/FEATURE_CHECKLIST.md](docs/agents/FEATURE_CHECKLIST.md)                                                           | соответствие реализованных функций требованиям                             |
| [docs/agents/README.md](docs/agents/README.md)                                                                                 | назначение каталога и правила ведения агентской документации               |




## Использование AI-инструментов

При разработке использовались Cursor и Codex как AI-ассистенты. Артефакты процесса сохранены в [docs/agents](docs/agents), [.agents/plans](.agents/plans) и [docs/superpowers](docs/superpowers).

Использованные модели: Cursor Grok 4.6, Composer 2.5, GPT-5.6 Luna, GPT-5.6 Terra, GPT-5.6 Sol, GPT-5.5 - в зависимости от задачи.

- Для анализа требований и декомпозиции использовались [Task.md](Task.md) (исходное задание в md формате), skills `brainstorming`, `writing-plans` и проектные правила, которые я описал на этапе создания harness в [AGENTS.md](AGENTS.md), а также skills конкретно для этого проекта ([fordewind-harness](.agents/skills/fordewind-harness), [fordewind-laravel](.agents/skills/fordewind-laravel) и другие).
- Для проверки применялись `test-driven-development`, `systematic-debugging`, `verification-before-completion`, Laravel Boost, Docker PHPUnit, Vite build, MCP Playwright.
- Каждую подзадачу я выполнял отдельной сессией для того чтобы не смешивать и не терять контекст агента.
- Код коммитился мной и проходил перед коммитом ручной отсмотр, проверку существующих контрактов и запуск соответствующих команд; итоговые решения по правкам и реализации принимались после этой проверки.



### Краткое резюме workflow:

1. Сначала был подготовлен каркас проекта: harness, skills, ТЗ преобразовано в технический план и декомпозировано на подзадачи. Архитектуру, детали реализации задавал я.
2. Затем был подготовлен Laravel, Docker Compose, установлен Laravel Boost, совмещен [AGENTS.md](AGENTS.md) настроенный в Laravel с написанным мной. Разложил все скиллы и доку для агента по папкам. Docker Compose был написан согласно моим правилам: ngninx способный корректно отдавать статику и поддерживать vite HMR, вынос env переменных и тд, небольшой размер образа и тд.
3. После этого взялся за backend: реализован импорт, серверные сценарии голосования и статистики.
  - Реализация шла по подзадачам, для того чтобы я мог верно описать требования к бэкэнду, и бизнес логике. Агент сам запускал нужные skills во время работы, так как я заранее это описал в [AGENTS.md](AGENTS.md) и связанной агентской доке. 
  - Код писался на основе тестов, что позволяло приходить к предсказуемым результатам, также было обозначано что агенту при окончании задачи нужно запускать суб-агентов для финального ревью. 
  - Далее каждая задача проходила мое ручное ревью, где я по пунктам обозначал правки которые нужно сделать, выполнял тесты и команды, отсматривал функционал сначала через api эндпоинты, в БД, и в UI.
4. На следующем этапе frontend разделён на jQuery-область голосования и Vue 3 Composition API статистики. Добавлены серверная пагинация, selector моделей и годов, адаптивный ezPlus, обработка ошибок загрузки фото, rate limit голосов.
  1. Здесь я обозначил то как нужно сделать front - использовать компонентный подход, однофайловые компоненты, script setup, обработка ошибок, composables и тд.
  2. Здесь актуально все то что описано в подпунктах 3, подход я использовал такой же. Отличие в том что, еще был подключен playwright, чтобы агент мог "видеть" то что реально происходит в UI

Решения по архитектуре, объёму, плану и приоритетам принимались после ручного ревью кода и подтверждались результатами тестов и сборки. Детальнее артефакты использования агента можно посмотреть в [таблице блока «Документация»](#документация).
