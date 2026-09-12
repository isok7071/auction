---
name: fordewind-laravel-design
overview: Подготовить production-ready Laravel-приложение для попарного голосования за фотографии аукционных автомобилей и просмотра статистики, с воспроизводимым Docker-окружением и документированным AI/harness workflow.
todos:
  - id: prepare-agent-context
    content: Поддержать .agents guidance, MCP templates и handoff artifacts
    status: completed
  - id: bootstrap-laravel
    content: Laravel 13 is installed. Remaining part of this todo is Docker Compose (Task 2 of the implementation plan)
    status: completed
  - id: write-project-guidance
    content: Create project-focused AGENTS.md and docs/agents workflow files without external evaluation framing
    status: completed
  - id: implement-import
    content: Implement optional GitLab source fetch and idempotent JSON/photo import command
    status: pending
  - id: implement-backend
    content: Implement models, migrations, focused services, repositories, requests, resources, and API routes
    status: pending
  - id: implement-frontend
    content: Implement jQuery voting page with ezPlus and Vue statistics page with full UI states
    status: pending
  - id: verify-and-document
    content: Run tests/build/smoke checks and write README plus AI workflow artifacts
    status: pending
isProject: false
---

# Fordewind Laravel Design Plan

> Historical design notes. Execution source of truth is `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md` and `docs/superpowers/plans/2026-09-11-fordewind-implementation.md`. Laravel 13 is already installed. Do not reinstall it. Next work is Docker (Task 2). Prefer Laravel conventions before creating `app/Domain` or `app/Infrastructure`. A Makefile is not required.

## Контекст

В [`Task.md`](../../Task.md) требуется Laravel + MySQL приложение с двумя frontend-зонами: jQuery для голосования и Vue.js для статистики. Laravel 13 уже стоит в корне репозитория вместе с `Task.md` и `.agents`.

Из ранее изученных Laravel/PHP-проектов берем практики:

- Thin controllers: контроллеры отвечают за HTTP-слой, validation и response, а не за бизнес-логику.
- Focused services: отдельные сервисы под импорт, выдачу пар, сохранение голосов и статистику.
- Repository boundaries: Eloquent-запросы и агрегации отделены от контроллеров и сервисной оркестрации там, где это улучшает тестируемость.
- DI/config conventions: зависимости собираются через Laravel container/providers, `env()` читается только в config.
- Artisan import command: импорт JSON и фотографий выполняется воспроизводимой командой.
- Docker/Makefile workflow: запуск, миграции, импорт и проверки должны иметь короткие документированные команды.
- AI workflow artifacts: решения, использованные инструменты, проверки и handoff фиксируются в `docs/agents`.

## Утвержденный Подход

Используем свежий Laravel 13 и минимальный custom Docker Compose (`nginx`, `php-fpm`, `mysql`, `node`/Vite build path). Версии PHP и MySQL в образах не зафиксированы в ТЗ: их выбирает агент на этапе Docker по `composer.json` и документации Laravel. Frontend строим как Blade + Vite entrypoints: отдельный jQuery-модуль для страницы голосования и отдельный Vue 3 app для страницы статистики.

Архитектура - Laravel-native modular monolith под конкретную задачу:

- `app/Domain/*`: Eloquent models, repository contracts, value objects/domain exceptions.
- `app/Services/*`: focused services вместо `Action`: `VotingPairService`, `VoteService`, `StatisticsService`, `CarImportService`.
- `app/Infrastructure/*`: Eloquent repositories, GitLab source fetcher, JSON reader, file storage adapter.
- `app/Http/*`: thin controllers, Form Requests, API Resources.

## Backend Design

Данные:

- `cars`: нормализованные поля для фильтров и отображения (`model`, `year`, базовые поля из JSON), `source_id`/hash, `raw_payload`.
- `car_photos`: `car_id`, исходное имя `Image`, публичный storage path, checksum/source hash.
- `votes`: `session_id`, `winner_photo_id`, `loser_photo_id`, `winner_car_id`, `loser_car_id`, timestamps.
- `sessions`: Laravel database session table для серверного состояния voting cycle.

Голосовательный цикл храним серверно через Laravel session с database session driver: для каждой связки `session_id + model` поддерживаем shuffled deck оставшихся `photo_id`. Когда в deck меньше двух фото, начинается новый цикл. Это закрывает требование “не повторять фото до исчерпания” без лишней таблицы состояния.

Импорт делаем идемпотентным через Artisan command. Команда должна уметь опционально скачать/обновить GitLab-источник из URL, но основной надежный режим - импорт из локального path, описанный в README.

## Frontend Design

Страница голосования:

- Blade layout + Vite entry `resources/js/voting.js`.
- jQuery управляет выбором модели, загрузкой пары, отправкой голоса, disabled/loading/error states.
- `ezPlus` используется только здесь, в режиме Tints.
- При нехватке фото показываем понятный empty state.

Страница статистики:

- Blade layout + отдельный Vue 3 mount в `resources/js/statistics.js`.
- Фильтры модели и годов обновляют данные без перезагрузки.
- Вывод таблицей или адаптивными карточками: фото, основные поля JSON, количество голосов.
- Отдельные состояния: loading, validation errors, network errors, empty result, total votes.

Адаптивность фиксируем в spec: desktop - две фотографии рядом и таблица статистики; mobile - вертикальное сравнение, доступные кнопки, карточный вывод статистики.

## Harness Flow

Workflow должен быть vendor-agnostic и пригодный для финальной сдачи:

- Поддерживать [`../README.md`](../README.md), [`../mcp/README.md`](../mcp/README.md), [`../mcp/servers.example.json`](../mcp/servers.example.json) и project skills в [`../skills`](../skills).
- После установки Laravel проверить, есть ли у starter project свой `AGENTS.md`/guidance, и объединить с [`../templates/AGENTS.md`](../templates/AGENTS.md) без внешнего evaluation framing.
- Добавить проектные правила о предметной области, Laravel conventions, слоях, DI/config, тестах, документации и запрете секретов.
- Вести `docs/agents/DECISIONS.md` для архитектурных решений.
- Вести `docs/agents/AI_WORKFLOW.md` на основе [`../templates/AI_WORKFLOW.md`](../templates/AI_WORKFLOW.md): skills, MCP/tools, subagents, prompts/reports и verification evidence.
- Вести `docs/agents/FEATURE_CHECKLIST.md` на основе [`../templates/FEATURE_CHECKLIST.md`](../templates/FEATURE_CHECKLIST.md), чтобы не пропустить требования из [`Task.md`](../../Task.md).

MCP-настройки храним как переносимые templates. Клиентские конфиги конкретного агента можно генерировать позже из [`../mcp/servers.example.json`](../mcp/servers.example.json), но не коммитить секреты.

## Проверка Качества

Минимальный verification набор:

- Unit tests: выбор пары, отсутствие повторов до исчерпания, reset цикла, подсчет голосов.
- Feature tests: endpoints голосования, сохранение голоса, фильтры статистики, импорт.
- Frontend/build: Vite build, basic lint/format если добавим tooling.
- Runtime smoke: Docker up, migrate, import, открыть обе страницы, проголосовать, проверить статистику.
- README review: запуск, env, Docker, миграции/импорт, технические решения, AI workflow artifacts.
- Security review: git diff не содержит секретов, токенов и реальных credentials.

## Следующие Шаги После Подтверждения

This section is historical. Do not reinstall Laravel. Follow `docs/superpowers/plans/2026-09-11-fordewind-implementation.md` from Task 2.
