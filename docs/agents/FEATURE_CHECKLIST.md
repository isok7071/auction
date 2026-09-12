# Feature Checklist

Use this checklist to track product coverage and review readiness.

Чеклист покрытия требований. Пункты про продукт читаются вместе с русским spec.

## Harness

- [x] Laravel 13 is installed without losing `Task.md` and `.agents`.
- [x] Root `AGENTS.md` is the operating manual (session protocol, skills, MCP, current stage). `HANDOFF.md` is only the live stage pointer.
- [x] Superpowers skills are vendored in `.agents/skills` and linked from `.codex/skills`.
- [x] MCP templates exist without secrets or host-specific paths.
- [x] `docs/agents/HANDOFF.md` records the current stage, next step, files, commands, verification, and risks.
- [x] `docs/agents/TOOLING.md` lists every project MCP server and skill.
- [x] `docs/agents/DECISIONS.md` records bootstrap and harness decisions.
- [x] `docs/agents/AI_WORKFLOW.md` records skills, tools, failures, and verification evidence.
- [x] Approved design spec exists in `docs/superpowers/specs` and is written in Russian for developers.
- [x] Implementation plan exists in `docs/superpowers/plans`.
- [x] README is Russian and points developers at the product spec and agents at the handoff files.
- [X] Git repository and commit history exist. Create only after explicit developer approval.

## Импорт данных

- [x] JSON-файлы источника можно импортировать в MySQL.
- [x] Фотографии копируются или отдаются через public storage.
- [x] Импорт идемпотентный.
- [x] Импорт работает из локального пути.
- [x] README описывает команды импорта.

## Страница голосования

- [x] Страница сделана на jQuery.
- [x] Пользователь выбирает модель автомобиля.
- [x] Сервер возвращает две разные случайные фотографии выбранной модели.
- [x] В одном цикле фотографии не повторяются, пока набор не исчерпан.
- [x] Пользователь может проголосовать за левую или правую фотографию.
- [x] Голос сохраняется на сервере.
- [x] Следующая пара загружается через AJAX без полной перезагрузки.
- [x] Увеличение фото работает через `ezPlus` в режиме Tints.
- [x] Реализованы состояния loading, disabled, error и empty; browser smoke пройден.

## Backend API

- [x] `GET /voting/models`, `GET /voting/pair` и `POST /voting/votes` реализованы как session/CSRF-защищённые JSON web routes.
- [x] Серверная колода предотвращает повтор фото до выдачи последнего доступного фото; pending-пара привязана к модели и browser session.
- [x] Голос сохраняется только для выданной pending-пары; повторная отправка после успешной записи отклоняется.
- [x] Отправка голосов ограничена серверным rate limiter по IP-адресу.
- [x] `GET /statistics/data` поддерживает точный ключ модели и включающий диапазон годов, возвращает zero-vote автомобили и `meta.total_votes`.
- [x] PHPUnit и Pint для backend-задач проходят в Docker runtime.

## Страница статистики

- [x] Страница сделана на Vue 3 Composition API и однофайловых компонентах.
- [x] Есть фильтр по модели автомобиля.
- [x] Есть фильтр по диапазону годов.
- [x] Фильтры обновляют результаты без полной перезагрузки.
- [x] Selector моделей показывает все модели, а результаты загружаются с серверной пагинацией по 24 автомобиля.
- [x] У каждого автомобиля есть фото, основные данные JSON и число голосов.
- [x] Показано общее число голосов по текущим фильтрам.
- [x] Реализованы состояния loading, error и empty; browser smoke пройден.

## Инфраструктура

- [x] Актуальная версия Laravel установлена и зафиксирована.
- [x] MySQL — основная база.
- [x] Docker Compose поднимает приложение локально.
- [x] `.env.example` содержит Docker MySQL defaults без секретов.
- [x] PHP 8.5, Node 24 LTS, MySQL 8.4 LTS и стабильный nginx зафиксированы в Docker.
- [x] Vite dev server поддерживает HMR/WebSocket, nginx отдаёт production-сборку.
- [x] Миграции и импорт воспроизводимы.

## Качество

- [x] Контроллеры тонкие.
- [x] Бизнес-логика вне контроллеров.
- [x] На этапе bootstrap соблюдены соглашения Laravel.
- [x] Unit-тесты цикла голосования и агрегации проходят в Docker runtime.
- [x] Feature-тесты голосования и статистики проходят в Docker runtime.
- [x] Production-сборка frontend проходит для отдельных jQuery и Vue SFC entrypoints.
- [x] Финальное read-only ревью backend, frontend, Docker и документации не выявило Critical-проблем; подтверждённые замечания исправлены.
- [x] README на русском описывает текущий запуск и указывает на AI-артефакты.
- [x] AI-артефакты заполнены для завершённого frontend-этапа.
