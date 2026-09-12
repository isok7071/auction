# Feature Checklist

Use this checklist to track product coverage and review readiness.

Чеклист покрытия требований. Пункты про продукт читаются вместе с русским spec.

## Harness

- [x] Laravel 13 is installed without losing `Task.md` and `.agents`.
- [x] Root `AGENTS.md` is the operating manual (session protocol, skills, MCP, current stage). `HANDOFF.md` is only the live stage pointer.
- [x] Superpowers skills are vendored in `.agents/skills` and linked from `.codex/skills`.
- [x] MCP templates exist without secrets or host-specific paths.
- [x] `docs/agents/HANDOFF.md` records stage, next step, files, commands, verification, and risks. Harness is complete; next vendor starts Docker.
- [x] `docs/agents/TOOLING.md` lists every project MCP server and skill.
- [x] `docs/agents/DECISIONS.md` records bootstrap and harness decisions.
- [x] `docs/agents/AI_WORKFLOW.md` records skills, tools, failures, and verification evidence.
- [x] Approved design spec exists in `docs/superpowers/specs` and is written in Russian for developers.
- [x] Implementation plan exists in `docs/superpowers/plans`.
- [x] README is Russian and points developers at the product spec and agents at the handoff files.
- [ ] Git repository and commit history exist. Create only after explicit developer approval.

## Импорт данных

- [ ] JSON-файлы источника можно импортировать в MySQL.
- [ ] Фотографии копируются или отдаются через public storage.
- [ ] Импорт идемпотентный.
- [ ] Импорт работает из локального пути.
- [ ] Импорт может опционально скачать или обновить исходный репозиторий.
- [ ] README описывает команды импорта.

## Страница голосования

- [ ] Страница сделана на jQuery.
- [ ] Пользователь выбирает модель автомобиля.
- [ ] Сервер возвращает две разные случайные фотографии выбранной модели.
- [ ] В одном цикле фотографии не повторяются, пока набор не исчерпан.
- [ ] Пользователь может проголосовать за левую или правую фотографию.
- [ ] Голос сохраняется на сервере.
- [ ] Следующая пара загружается через AJAX без полной перезагрузки.
- [ ] Увеличение фото работает через `ezPlus` в режиме Tints.
- [ ] Есть состояния loading, disabled, error и empty.

## Страница статистики

- [ ] Страница сделана на Vue.js.
- [ ] Есть фильтр по модели автомобиля.
- [ ] Есть фильтр по диапазону годов.
- [ ] Фильтры обновляют результаты без полной перезагрузки.
- [ ] У каждого автомобиля есть фото, основные данные JSON и число голосов.
- [ ] Показано общее число голосов по текущим фильтрам.
- [ ] Есть состояния loading, error и empty.

## Инфраструктура

- [x] Актуальная версия Laravel установлена и зафиксирована.
- [x] MySQL — основная база.
- [x] Docker Compose поднимает приложение локально.
- [x] `.env.example` содержит Docker MySQL defaults без секретов.
- [x] PHP 8.5, Node 24 LTS, MySQL 8.4 LTS и стабильный nginx зафиксированы в Docker.
- [x] Vite dev server поддерживает HMR/WebSocket, nginx отдаёт production-сборку.
- [ ] Миграции и импорт воспроизводимы.

## Качество

- [ ] Контроллеры тонкие.
- [ ] Бизнес-логика вне контроллеров.
- [x] На этапе bootstrap соблюдены соглашения Laravel.
- [ ] Unit-тесты покрывают цикл голосования и агрегацию статистики.
- [ ] Feature-тесты покрывают голосование, статистику и импорт.
- [x] Сборка frontend проходит для стандартного Laravel scaffold.
- [x] README на русском описывает текущий запуск и указывает на AI-артефакты.
- [x] AI-артефакты заполнены для этапа harness.
