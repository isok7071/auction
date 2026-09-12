# Fordewind Implementation Plan

> **For agentic workers:** Follow `AGENTS.md`. Then execute this plan from the current `HANDOFF.md` stage. Use `executing-plans` in this session, or `subagent-driven-development` only if the runtime actually supports subagents. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the Fordewind Laravel voting application from `Task.md` with Docker, import, voting, statistics, tests, README, and AI workflow artifacts.

**Architecture:** Laravel 13 modular monolith with thin controllers and focused services. jQuery owns the voting page; Vue 3 owns the statistics page. MySQL is the main runtime database through Docker Compose.

**Tech Stack:** Laravel 13 (installed), PHP and MySQL in Docker with versions chosen at Task 2 from `composer.json` and current Laravel docs, Blade, Vite, Tailwind, jQuery, ezPlus, Vue 3, PHPUnit, Docker Compose. Do not treat earlier PHP 8.4 / MySQL 8 draft notes as locked.

## Global Constraints

- Follow Laravel Boost guidance in `AGENTS.md`.
- Follow Fordewind project rules in `AGENTS.md`.
- Keep `env()` reads in config files only.
- Do not mix jQuery and Vue in one functional area.
- Do not commit `.env`, tokens, credentials, downloaded secrets, or local private configs.
- Use TDD for product behavior: write a failing test before production feature code.
- Record decisions and verification evidence in `docs/agents`.

---

## Task 1: Bootstrap Laravel And Harness

**Files:**

- Created/modified: root Laravel scaffold files.
- Modified: `AGENTS.md`
- Create: `docs/agents/TOOLING.md`
- Create: `docs/agents/HANDOFF.md`
- Create: `docs/agents/DECISIONS.md`
- Create: `docs/agents/AI_WORKFLOW.md`
- Create: `docs/agents/FEATURE_CHECKLIST.md`
- Create: `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md`
- Create: `docs/superpowers/plans/2026-09-11-fordewind-implementation.md`

**Interfaces:**

- Produces: Laravel 13 application root and vendor-agnostic AI handoff artifacts.
- Consumes: `Task.md`, `.agents/plans/fordewind-laravel-design.md`, `.agents/templates/*`.

- [x] **Step 1: Read Laravel agent bootstrap instructions**

Run: fetch `https://laravel.com/for/agents`.

Expected: instructions confirm checking existing app, prerequisites, Laravel install, Boost install, and frontend dependency verification.

- [x] **Step 2: Check prerequisites**

Run:

```bash
php -v; composer -V; laravel --version; npm -v; bun -v; git status --short --branch
```

Expected: PHP and Composer available, npm available, Laravel CLI optional if Composer bootstrap is used.

- [x] **Step 3: Create Laravel skeleton without overwriting existing artifacts**

Run:

```bash
COMPOSER_ALLOW_SUPERUSER=1 composer create-project laravel/laravel .laravel-bootstrap --no-interaction
rsync -a .laravel-bootstrap/ ./ --exclude=.env --exclude=.git
rm -rf .laravel-bootstrap
```

Expected: root contains Laravel scaffold plus existing `Task.md` and `.agents`.

- [x] **Step 4: Install Laravel Boost**

Run:

```bash
COMPOSER_ALLOW_SUPERUSER=1 composer require laravel/boost --dev --no-interaction
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate --ansi
php artisan boost:install --no-interaction
```

Expected: `AGENTS.md` and `CLAUDE.md` contain Boost guidelines. Cursor-specific `.cursor` writes may fail in read-only environments and should be recorded, not forced.

- [x] **Step 5: Install and build frontend scaffold**

Run:

```bash
npm install --ignore-scripts
npm run build
```

Expected: dependencies install and Vite build writes `public/build`.

- [x] **Step 6: Initialize harness artifacts**

Create `docs/agents` and record decisions, workflow evidence, and feature coverage.

Expected: a later vendor can continue without relying on chat history.

- [x] **Step 7: Complete the portable harness before infrastructure**

Create `docs/agents/HANDOFF.md`, `docs/agents/TOOLING.md`, portable MCP templates, and gitignore machine-specific MCP configs.

Expected: another vendor can start from files only. This step does not start Docker/MySQL.

## Task 2: Docker And Environment

**Gate:** Harness is complete. Start this task. The next vendor implements Docker before import or product features.

**Files:**

- Create: `docker-compose.yml`
- Create: `docker/php/Dockerfile`
- Create: `docker/nginx/default.conf`
- Modify: `.env.example`
- Modify: `README.md`
- Modify: `AGENTS.md` current-stage block
- Modify: `docs/agents/HANDOFF.md`
- Modify: `docs/agents/DECISIONS.md`
- Modify: `docs/agents/AI_WORKFLOW.md`
- Modify: `docs/agents/FEATURE_CHECKLIST.md`

**Interfaces:**

- Produces: Docker Compose services for app, nginx, mysql, and frontend build path.
- Consumes: Laravel root scaffold.

- [x] **Step 1: Write environment test/check**

Run:

```bash
docker compose config
```

Expected before implementation: fails because `docker-compose.yml` does not exist.

- [x] **Step 2: Add Docker Compose**

Add PHP-FPM, nginx, MySQL, and a Vite/node build path. Pick PHP, extensions, and MySQL versions from `composer.json` (`php: ^8.3` at bootstrap), Laravel docs, and what the app actually needs. Point `.env.example` at MySQL. Record the chosen versions in `docs/agents/DECISIONS.md`. Update `AGENTS.md` and `HANDOFF.md` so the live stage matches Docker, not harness.

- [x] **Step 3: Verify Docker config**

Run:

```bash
docker compose config
```

Expected: config renders without errors.

## Task 3: Data Model And Import

**Gate:** Docker/MySQL from Task 2 is up. Do not start import against the host SQLite `.env`.

**Files:**

- Create migrations for `cars`, `car_photos`, `votes`, and the Laravel `sessions` table if the session driver is database.
- Point session/database config at the Docker MySQL settings from Task 2.
- Create models and factories for `Car`, `CarPhoto`, and `Vote`.
- Create `app/Services/CarImportService.php`.
- Create infrastructure readers/storage adapters as needed.
- Create PHPUnit tests for idempotent import.

**Interfaces:**

- Produces: imported cars/photos available through stable storage paths.
- Consumes: local source data path or optional fetched repository.

- [ ] **Step 1: Write failing import tests**
- [ ] **Step 2: Implement migrations/models/import service**
- [ ] **Step 3: Add Artisan import command**
- [ ] **Step 4: Verify with PHPUnit and record evidence**

## Task 4: Voting Backend

**Files:**

- Create `VotingPairService`.
- Create `VoteService`.
- Create Form Requests and API Resources.
- Add routes/controllers for model list, pair retrieval, and vote submission.
- Create unit and feature tests.

**Interfaces:**

- Produces: AJAX endpoints for the jQuery voting page.
- Consumes: `cars`, `car_photos`, `votes`, and Laravel session state.

- [ ] **Step 1: Write failing tests for pair selection and no-repeat cycle**
- [ ] **Step 2: Implement minimal services**
- [ ] **Step 3: Write failing endpoint tests**
- [ ] **Step 4: Implement controllers/routes/resources**
- [ ] **Step 5: Verify with PHPUnit and record evidence**

## Task 5: Statistics Backend

**Files:**

- Create `StatisticsService`.
- Add statistics controller, request, and resource.
- Create aggregation tests and feature tests.

**Interfaces:**

- Produces: API data for Vue statistics UI.
- Consumes: vote and car/photo tables.

- [ ] **Step 1: Write failing aggregation tests**
- [ ] **Step 2: Implement minimal statistics service**
- [ ] **Step 3: Write failing endpoint tests**
- [ ] **Step 4: Implement controller/routes/resources**
- [ ] **Step 5: Verify with PHPUnit and record evidence**

## Task 6: Voting Frontend

**Files:**

- Modify/create Blade voting view.
- Create `resources/js/voting.js`.
- Modify Vite config if a new entrypoint is needed.
- Add `jquery` and `ez-plus`. They are required by `Task.md`. Ask before adding other new dependencies.

**Interfaces:**

- Produces: responsive jQuery voting page.
- Consumes: voting backend endpoints.

- [ ] **Step 1: Add frontend dependencies**
- [ ] **Step 2: Implement loading/error/empty/success states**
- [ ] **Step 3: Enable `ezPlus` Tints zoom**
- [ ] **Step 4: Build frontend and smoke check page**

## Task 7: Statistics Frontend

**Files:**

- Modify/create Blade statistics view.
- Create `resources/js/statistics.js`.
- Modify Vite config if a new entrypoint is needed.
- Add `vue`. It is required by `Task.md`. Ask before adding other new dependencies.

**Interfaces:**

- Produces: responsive Vue statistics page.
- Consumes: statistics API endpoint.

- [ ] **Step 1: Add frontend dependency**
- [ ] **Step 2: Implement model/year filters and states**
- [ ] **Step 3: Render car rows/cards and total votes**
- [ ] **Step 4: Build frontend and smoke check page**

## Task 8: README And Final Verification

**Files:**

- Modify: `README.md`
- Modify: `AGENTS.md` current-stage block
- Modify: `docs/agents/HANDOFF.md`
- Modify: `docs/agents/DECISIONS.md`
- Modify: `docs/agents/AI_WORKFLOW.md`
- Modify: `docs/agents/FEATURE_CHECKLIST.md`

**Interfaces:**

- Produces: final handoff instructions and verification evidence.
- Consumes: all implemented app behavior.

- [ ] **Step 1: Run backend tests**
- [ ] **Step 2: Run frontend build**
- [ ] **Step 3: Run Docker smoke checks**
- [ ] **Step 4: Review docs and checklist**
- [ ] **Step 5: Review git diff for secrets**
