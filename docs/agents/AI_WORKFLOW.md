# AI Workflow

This document records how AI-assisted engineering was used on the Fordewind project.

## Workflow Summary

- Planning: Requirements from `Task.md` were converted into `.agents/plans/fordewind-laravel-design.md` and copied into `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md`. The backend plan was refreshed after inspecting the actual source JSON/JPG repository: 1,497 JSON, 1,497 JPG, all image references present; it now defines migrations, local idempotent import, session-backed voting, statistics, endpoint contracts, and test gates. After Tasks 1–2 completed, the continuation plan was aligned to start at Task 3; it retains the final odd photo at the cycle boundary and serializes pair/vote AJAX routes through Laravel session blocking.
- Implementation: Laravel 13, Docker Compose, import, jQuery voting, and Vue statistics are implemented. The statistics page is split into Composition API single-file components, and the voting page keeps jQuery and ezPlus isolated.
- Review: Major decisions are recorded in `docs/agents/DECISIONS.md`.
- Verification: Commands and results are recorded below as they are run.
- Documentation: `AGENTS.md` is the operating manual. `docs/agents/HANDOFF.md` is the live stage pointer.

## Skills Used

| Skill | Purpose | Result |
| --- | --- | --- |
| `using-superpowers` | Enforce explicit skill-driven workflow | Active for the session. |
| `brainstorming` | Confirm design-before-implementation discipline | Existing design plan was used as the approved design source. |
| `writing-plans` | Keep implementation staged and handoff-ready | Current work split into bootstrap, harness, and verification stages. |
| `systematic-debugging` | Investigate Boost command and Vite build failures | Root causes identified before continuing. |
| `test-driven-development` | Establish test-first constraint for future feature work | No production feature code has been written in this bootstrap stage. |
| `verification-before-completion` | Require command evidence before completion claims | Verification commands are recorded below. |
| `fordewind-laravel` | Apply project-specific Laravel standards | Root `AGENTS.md` now includes Fordewind architecture and frontend boundaries. |
| `fordewind-harness` | Maintain vendor-agnostic AI workflow artifacts | `docs/agents` now includes HANDOFF, TOOLING, decisions, workflow log, checklist, and MCP mapping. |
| `executing-plans` | Execute Task 2 from the written implementation plan | Docker Compose files, env example, README, decisions, workflow, and handoff were updated. |
| `using-git-worktrees` | Check isolation before executing the plan | Repository is not initialized as git, so work continued in the current directory. |
| `caveman` | Keep developer communication compact while preserving technical accuracy | Active for the Docker correction review. |

## Final Review Follow-up - 2026-09-12

- Product fixes: voting cards switch to two columns only from `lg`; tablet and mobile use a contained ezPlus lens. Statistics year filters are native selectors containing years only, from 1886 through the current year.
- Reliability: stale ezPlus event handlers and pending image load handlers are removed before each new pair; an image loading failure keeps vote buttons disabled and shows an error. Vote submissions are limited to 30 requests per minute per IP address.
- Review: two independent read-only reviews covered backend queries/architecture and frontend/docs. No Critical issue was reported. The `CONCAT(make, model)` selector query and concurrent manual-import race remain documented review considerations rather than speculative schema/operational changes.
- Documentation: README now has ordered clean-clone setup, import, Vite development, production build, checks, and shutdown commands. Stale agent handoff references were corrected.
- Browser MCP could not use its configured system Chrome in the final environment because installation requires host `sudo`. A temporary Playwright Chromium runtime was installed without changing project dependencies and completed the final visual smoke.

## Current Review Follow-up - 2026-09-13

- `Car::modelKey()` is now the common PHP representation of `make + ' ' + model`; vote validation, pending-pair validation, and the statistics resource use it instead of duplicating the expression.
- Statistics year validation names its integer-only pattern. The request sequence comment documents why it remains necessary in addition to `AbortController`.
- Voting waits for both photo `load` events before enabling its buttons. The same request token now invalidates stale AJAX, image, and delayed ezPlus callbacks after a model switch.
- No schema migration was added. README records generated `model_key` and year indexes as scalability work to validate with query plans if the dataset grows.
- Test review removed the two Laravel skeleton tests and a CSS-class assertion. HTTP coverage now proves pending-pair reuse in one browser session and verifies both the vote response and persisted photo IDs.

## MCP And External Tools Used

| Tool | Purpose | Result |
| --- | --- | --- |
| Web fetch: `https://laravel.com/for/agents` | Read current Laravel agent bootstrap guidance | Used as setup source of truth. |
| Laravel Boost | Generate Laravel-aware agent guidance | Installed as `laravel/boost v2.8.1`; shared guidance written. |
| Laravel Boost MCP `application_info` | Confirm installed Laravel/PHP/package versions before Docker choices | Reported PHP `8.3`, Laravel `13.31.0`, SQLite local default before Docker env update. |
| Context7 | Attempt current Laravel documentation lookup | Failed with `fetch failed`; used installed package metadata and official docs fallback. |
| PHP supported versions | Verify the current supported PHP branch | PHP 8.5 confirmed as actively supported current stable. |
| Node.js releases | Select a production-supported Node line | Node 24 confirmed as LTS; Node 26 is Current. |
| MySQL releases | Verify the database support track | MySQL 8.4 confirmed as LTS. |
| nginx downloads | Verify the stable nginx branch | nginx 1.30 confirmed as stable. |
| Vite server documentation | Verify current HMR/WebSocket configuration | Used Vite 8 `server.ws`, `origin`, `host`, `port`, and `strictPort` options. |

## Subagents Used

- `task12_code_review`: read-only Laravel review of Tasks 1–2. It checked architecture, DTO naming, model metadata, N+1 safety, production schema, and tests. Its Critical and Important findings were fixed; the repeated review returned no remaining Critical or Important issues.
- `backend_review`: read-only review of Tasks 3–5. It found no Critical production defect, identified incomplete vote/statistics regression coverage, and noted that `distinct` does not compare two scalar request fields. The missing cases were added and `loser_photo_id` now uses `different:winner_photo_id`; reversed IDs of the issued pair remain valid because either photo may be the user-selected winner.

## Prompts And Reports

- Developer asked to start implementing `.agents/plans/fordewind-laravel-design.md`, beginning with Laravel installation and harness setup.
- Developer clarified that PHP should not be reinstalled. The workflow switched from `php.new` to Composer-based Laravel bootstrap.
- Developer asked to complete a full harness before infrastructure so another vendor can continue without chat history.
- Developer asked why docs were English and required Russian for README plus product/business documentation. Agent harness files stay English.
- Developer asked to enable every MCP from the project templates, including memory and thinking.
- Developer asked to configure Codex MCP in this project if possible.
- Developer asked to vendor Superpowers into the repository so Codex and other vendors can use the skills without a Cursor plugin.
- Developer asked for a complete MCP and skills inventory, to mark the harness done, and to send the next vendor to Docker.
- Developer rejected pinning PHP 8.4 (or other concrete Docker dependency versions) in handoff. The next agent chooses what the stack needs.
- Developer required `AGENTS.md` to be the operating manual so a new vendor does not need a pasted file list.
- Review of the agent plan found stale design notes, a required-subagent header, missing Codex skill links, and “read HANDOFF first” leftovers. Those were corrected.
- Developer asked the next stage to build the Docker Compose image with optimized image practices and to use skills/subagents/MCP as needed.
- Developer required current supported PHP and Node versions, Compose environment values moved to an env file, and correct nginx/Vite/Vue static and HMR handling. The approved correction uses PHP 8.5 and Node 24 LTS.
- Developer requested a decomposed Laravel-style backend plan. The source GitLab snapshot was cloned read-only into `/tmp`; its commit, JSON field names, import key (`AuctionItemId`), and 73 exact `Make + Model` selector values were used to replace the stale historical `.agents` plan without changing product code.
- Developer required restoring the previously agreed `Domain / Infrastructure / Repository` architecture. The backend plan and D019 now define contracts and Eloquent/storage implementations per Cars, Voting, and Statistics domain, with readonly DTOs only for compound cross-layer data.
- Developer requested a step-by-step README import procedure. README now lists directory creation, source clone/update, Compose startup, migrations, storage link, and `cars:import` commands.
- Developer requested the backend continuation plan after confirming that the application and local import command work. The plan now uses the completed model/import baseline, defines the Task 3–6 backend deliverables, and corrects the five-photo cycle so its final unseen photo is issued before reuse.
- Developer requested completing all backend logic and synchronizing the agent artifacts. Tasks 3–5 code plus README, checklist, decision, workflow, and handoff updates were added; runtime verification remains an explicit next step because the environment cannot access Docker.
- Tasks 3–5 are implemented in the working tree: pair selection, pending-pair vote persistence, and filtered statistics use Domain contracts, Eloquent repositories, services, Form Requests, Resources, named web routes, and focused unit/feature tests. `Car::selectModelKey()` and `Car::whereModelKey()` centralize the MySQL `CONCAT()` model-key query logic used by voting and statistics. Docker PHPUnit/Pint verification is currently blocked by sandbox denial of `/var/run/docker.sock`; host verification is blocked by PHP 8.3 versus the project's PHP 8.5 requirement. `php -l` and `git diff --check` pass for the changed files.

## Verification Evidence

| Check | Command | Result |
| Data model TDD red | `docker compose run --rm app php artisan test --compact tests/Feature/Database/CarDomainSchemaTest.php` | Failed as expected: `App\\Domain\\Cars\\Models\\Car` did not exist. |
| Data model TDD green | Same command after migrations, models, factories, and explicit `UseFactory` attributes | Passed: 1 test, 3 assertions. |
| Domain factory diagnosis | Read Laravel `HasFactory` and `Factory::resolveFactoryName()` source | Default resolver treated `App\\Domain\\Cars\\Models\\Car` as `Database\\Factories\\Domain\\Cars\\Models\\CarFactory`; `#[UseFactory(...)]` is required for the chosen Domain namespace. |
| MySQL migration status | `docker compose exec -T app php artisan migrate:status` | All three Fordewind migrations are applied in MySQL batch 1. |
| Data model formatting | `docker compose run --rm app vendor/bin/pint --dirty --format agent` | Passed. |
| Import TDD red | `docker compose run --rm app php artisan test --compact tests/Feature/Console/ImportCarsCommandTest.php` | Failed as expected: `The command "cars:import" does not exist.` |
| Import TDD green/full suite | `docker compose run --rm app php artisan test --compact` | Passed: 4 tests, 17 assertions. |
| Import formatting | `docker compose run --rm app vendor/bin/pint --dirty --format agent` | Passed. |
| Schema normalization | `docker compose exec -T app php artisan migrate:fresh --force` | Rebuilt the local schema from the corrected initial migration; no raw JSON column or cleanup migration. |
| Task 1–2 architecture refactor | Rename import data objects with `Dto`; add Eloquent field constants/PHPDoc; replace field-name strings; enable non-production lazy-loading prevention | Completed. Import service counters now use explicit initialization/control flow; repository returns `UpsertedCarDto` instead of leaking Eloquent state to orchestration. |
| Task 1–2 focused verification | `docker compose exec -T app php artisan test --compact tests/Feature/Database/CarDomainSchemaTest.php tests/Feature/Console/ImportCarsCommandTest.php` | Passed: 3 tests, 16 assertions. |
| Task 1–2 formatting | `docker compose exec -T app vendor/bin/pint --dirty --format agent` | Completed; Pint normalized all changed PHP files. |
| Task 1–2 independent review | `task12_code_review` read-only worktree review, then repeated review after fixes | Found inconsistent default `VoteFactory` relationships and coverage gaps; all Critical/Important findings fixed. Final verdict: ready for Task 3. |
| VoteFactory regression RED/GREEN | Focused `CarDomainSchemaTest::test_vote_factory_links_each_photo_to_its_recorded_car` | RED proved `winner_car_id=1` while photo car was `3`; GREEN passed after deriving car IDs from resolved photos. |
| Final Task 1–2 suite | `docker compose exec -T app php artisan test --compact` | Passed: 10 tests, 25 assertions. |
| Post-normalization suite | `docker compose run --rm app php artisan test --compact` | Passed: 4 tests, 17 assertions; Pint and `git diff --check` passed. |
| Tasks 3–5 PHPUnit attempt | `docker compose run --rm app php artisan test --compact tests/Unit/Services/VotingPairServiceTest.php tests/Feature/Http/VotingPairEndpointTest.php` | Not run: sandbox denies access to `/var/run/docker.sock`. |
| Tasks 3–5 host PHPUnit fallback | `php artisan test --compact tests/Unit/Services/VotingPairServiceTest.php` | Not run: Composer platform check rejects host PHP 8.3; project requires PHP 8.5. |
| Tasks 3–5 static verification | `php -l` for changed PHP files; `git diff --check`; secret scan excluding vendored instruction examples and `.env.example` | Passed: every checked PHP file has valid syntax; no diff whitespace errors or project-secret matches. |
| Tasks 3–5 independent review | `backend_review` read-only code review | No Critical defect; regression coverage and scalar-photo validation finding fixed before handoff. |
| Model-key scope refactor | `php -l app/Domain/Cars/Models/Car.php app/Infrastructure/Persistence/Eloquent/EloquentVotingRepository.php app/Infrastructure/Persistence/Eloquent/EloquentStatisticsRepository.php`; `git diff --check` | Passed. `Car` now owns MySQL-only `selectModelKey()` and `whereModelKey()` scopes; no SQLite branch remains in this query logic. |
| Current review follow-up | `docker compose exec -T app php artisan test --compact tests/Unit/Services/VoteServiceTest.php tests/Unit/Services/VotingPairServiceTest.php tests/Unit/Services/StatisticsServiceTest.php tests/Feature/Http/VoteEndpointTest.php tests/Feature/Http/VotingPairEndpointTest.php tests/Feature/Http/StatisticsEndpointTest.php tests/Feature/Database/CarDomainSchemaTest.php` | Passed: 30 tests, 126 assertions. |
| Current frontend build | `docker compose run --rm node npm run build` | Passed after the jQuery pair lifecycle change; Vite 8.3.0 emitted the production bundles. |
| Test review follow-up | `docker compose exec -T app php artisan test --compact` | Passed: 36 tests, 158 assertions. |
| --- | --- | --- |
| Prerequisite versions | `php -v; composer -V; laravel --version; npm -v; bun -v; git status --short --branch` | PHP 8.3.17, Composer 2.6.2, npm 10.9.8 available; `laravel` and `bun` unavailable; directory was not a git repository. |
| Laravel agent guidance | Fetch `https://laravel.com/for/agents` | Guidance read before installation. |
| Remote PHP installer | `/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"` | Blocked for approval, then interrupted by the developer; not used. |
| Composer bootstrap, first try | `COMPOSER_ALLOW_SUPERUSER=1 composer create-project laravel/laravel .laravel-bootstrap --no-interaction` | Failed with proxy `403 after CONNECT` while contacting Packagist. |
| Composer bootstrap, retry | Same command with full network access | Installed `laravel/laravel v13.10.1` and `laravel/framework v13.31.0`; temporary SQLite migrate warned about missing SQLite driver. |
| Laravel Boost install, first try | `COMPOSER_ALLOW_SUPERUSER=1 composer require laravel/boost --dev --no-interaction && php artisan boost:install --no-interaction` | Dependency installed, but `boost:install` command was unavailable because the app ran as `production` without `.env`. |
| Boost root cause check | `php artisan env; php artisan config:show boost --no-interaction` | Confirmed environment was `production`; Boost requires `local` or debug mode to register commands. |
| Boost install, retry | `php -r "file_exists('.env') || copy('.env.example', '.env');" && php artisan key:generate --ansi && php artisan boost:install --no-interaction` | App key generated; Boost installed shared guidelines. Cursor-specific skill/MCP writes failed because `.cursor` was read-only. |
| Frontend dependencies | `npm install --ignore-scripts` | Installed 91 packages; audit found 0 vulnerabilities. |
| Vite build, first try | `npm run build` | Failed because `fonts.bunny.net` DNS lookup returned `EAI_AGAIN`. |
| Vite build, retry | Same command with full network access | Build completed and wrote assets under `public/build`. |
| Composer metadata | `COMPOSER_ALLOW_SUPERUSER=1 composer validate --strict` | Passed; `composer.json` is valid. |
| Laravel boot | `php artisan about` | Passed; Laravel `13.31.0`, PHP `8.3.17`, environment `local`. |
| Vite build, fresh verification | `npm run build` | Passed; production assets written under `public/build`. |
| Default Laravel tests | `php artisan test --compact` | Passed; 2 tests, 2 assertions. |
| Harness secret/path scan | ripgrep `/home/isok` and `wsl.exe` in committed templates | Absolute host paths remain only in gitignored `.mcp.json` and `opencode.json`. Portable examples use `php artisan boost:mcp`. |
| MCP smoke | start `php artisan boost:mcp`, sequential-thinking, knowledge-graph; `docker image inspect mcp/fetch` | All four stdio servers stayed up; `mcp/fetch` image is present. |
| Codex project MCP | write `.codex/config.toml`; `codex mcp list` | Project file contains Boost, fetch, browser, thinking, memory without host paths. `codex mcp list` shows only user-level servers, which is a known Codex listing gap; runtime still loads trusted project config. |
| Agent plan audit | compare AGENTS.md, HANDOFF, spec, plan, `.codex/skills`, `composer.json` | Codex skills were missing Fordewind/Boost (now linked, 21 total). Historical design still said Laravel was missing. Plan required subagents. AI_WORKFLOW still pointed at HANDOFF first. Those traps were corrected. |
| Docker compose missing baseline | `docker compose config` | Failed as expected before Task 2 with `no configuration file provided: not found`. |
| Docker compose config | `docker compose config` | Passed after adding `docker-compose.yml`, PHP-FPM build, nginx, and MySQL services. |
| Docker compose assets profile config | `docker compose --profile assets config` | Passed and rendered the profiled `node` service for Vite/npm commands. |
| Docker app image build | `docker compose build app` | Passed; built `fordewind-app:php-8.3` with image id `5effbe19defb`. Docker warned that git commit metadata is unavailable because the project is not a git repository. |
| Docker PHP extensions | `docker compose run --rm --no-deps app php -m` | Passed; key runtime extensions include `intl`, `mbstring`, `pcntl`, `PDO`, `pdo_mysql`, `zip`, and `Zend OPcache`. |
| Docker Composer smoke | `docker compose run --rm --no-deps app composer --version` | Passed; Composer `2.10.3`, PHP `8.3.33`. |
| Docker Laravel smoke | `docker compose run --rm --no-deps app php artisan about` | Passed; Laravel `13.31.0`, PHP `8.3.33`, URL `localhost:8080`, database driver `mysql`. |
| Docker Node smoke | `docker compose run --rm --no-deps node node --version` | Passed; profiled `node` service can be targeted directly and reports Node `v24.21.0`. |
| PHP 8.5 lock refresh | `docker compose run --rm --no-deps app composer update --lock --no-install --no-interaction` | Passed; lock metadata updated without dependency changes or vulnerabilities. |
| Initial optimized image check | build Debian PHP 8.5 image and inspect layers | Functional but about 560 MB; deleting compilers in a child layer did not reclaim base-layer bytes. |
| Alpine PHP image build | `docker compose build app` | Passed; `fordewind-app:php-8.5` built from `php:8.5-fpm-alpine`, about 138 MB. |
| PHP 8.5 runtime | `php -v`, `php -m`, `composer check-platform-reqs` in app container | PHP 8.5.10; required extensions and all Composer platform requirements passed; `gcc` is absent at runtime. |
| Alpine Node diagnosis | run Vite in `node:24-alpine` with mounted dependencies | Failed with missing `@rolldown/binding-linux-x64-musl`; host dependencies contain the glibc native binding. |
| Vite HMR smoke | run `node:24-bookworm-slim`; fetch `/@vite/client`; open WebSocket using protocol `vite-hmr` | Passed; HTTP 200 and WebSocket opened on port 5173. |
| nginx static smoke | `nginx -t`; request `/build/manifest.json` inside nginx container | Passed on nginx 1.30.4; HTTP 200 with `Cache-Control: public, max-age=31536000, immutable`. |
| Docker Laravel 8.5 smoke | `docker compose exec -T app php artisan about --only=environment` | Passed; Laravel 13.31.0 on PHP 8.5.10 with URL `localhost:8080`. |
| Docker tests after env centralization | `docker compose run --rm --no-deps app php artisan test --compact` | Initially failed because Compose's database session driver overrode PHPUnit defaults; `phpunit.xml` now forces isolated testing values. |
| Writable Laravel paths diagnosis | HTTP smoke plus container `id`/`stat` and logs | Blade volume permissions fixed with named volumes and entrypoint; remaining 500 traced to stale local `.env` using SQLite and file logging. `.env.example` now defaults logs to stderr; existing `.env` must be updated by the developer. |
| Clean README run | Removed Fordewind containers/custom image and all project volumes, then followed README from `cp .env.example .env` through build, dependency install, startup, migration, Vite build, and dev server | Passed. PHP 8.5 image rebuilt; Composer and npm installed; MySQL healthy; migrations 1-3 ran; Laravel HTTP `200`; Vite `/@vite/client` HTTP `200`; HMR WebSocket opened; Laravel tests `2 passed`. |

## Open Setup Notes

- The repository is initialized on `main` but has no commits. Create history only after explicit developer approval.
- The current local `.env` may still contain scaffold values from bootstrap. `.env.example` now points at Docker MySQL; copy it before Docker runtime setup.
- Backend, import, jQuery voting UI, and Vue statistics UI are implemented and browser-verified.
- Laravel Cloud CLI was not installed globally because deployment is outside the current local setup stage.
- `.mcp.json` and `opencode.json` are local/gitignored. Portable mappings are `.mcp.json.example` and `opencode.json.example`.
- MCP servers configured: `laravel-boost`, `fetch` (Docker `mcp/fetch`), `browser`, `thinking`, `memory`. No separate `context` process; Boost covers Laravel context.
- Codex project MCP lives in committed `.codex/config.toml` and loads only when Codex trusts this project.
- Because there is no initial commit, `git status --short` is more useful than `git diff` for the current tree.

## Human Review Notes

- Do not reinstall PHP with `php.new`.
- Docker delivery versions are now recorded in D017: PHP 8.5, Node 24 LTS, MySQL 8.4 LTS, and stable nginx 1.30.
- `AGENTS.md` is the operating manual. A pasted vendor-switch prompt is not required.
- Backend and both frontend areas are implemented. The next action is developer review; no commit was created.
- Every detail needed for a vendor switch must live in project files.
- README and product/business documentation must be Russian; agent harness stays English.

## Frontend Implementation Evidence - 2026-09-12

| Check | Command | Result |
| --- | --- | --- |
| Frontend route and statistics contracts | `docker compose exec app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php tests/Feature/Http/StatisticsEndpointTest.php` | Passed: 7 tests, 36 assertions after the Vue SFC refactor. |
| Full application suite | `docker compose exec app php artisan test --compact tests/Feature/Console tests/Feature/Database tests/Unit/Services tests/Feature/Http` | Passed after the Vue SFC refactor: 31 tests, 113 assertions. |
| Vue SFC production build | `docker compose --profile assets run --rm --user 1000:1000 node npm run build` | Passed with Vite 8.3.0; emitted separate `statistics` and `voting` bundles. |
| Vue architecture | Inspect `resources/js/statistics` and Vite config | Composition API `<script setup>` SFCs split presentation, API transport, and request/filter state; `@vitejs/plugin-vue` 6.0.8 added. |
| Runtime data | `php artisan migrate --force`; `php artisan storage:link`; `php artisan cars:import storage/app/import-source` in app container | No pending migrations; storage link already existed; full import processed 1,497 cars/photos idempotently. |
| HTTP smoke | request `/`, `/statistics`, `/statistics/models`, `/statistics/data` through nginx | All returned HTTP 200. |
| Browser smoke | Browser MCP navigation to `http://localhost:8080/` | Initial attempt was blocked by missing Chrome; after installation the complete browser smoke passed. |
| Browser smoke after Chrome installation | Playwright at `http://localhost:8080/` | Passed: voting pair, ezPlus data, vote-to-next-pair flow, statistics filters/validation/empty result, desktop table, mobile cards, zero console errors, and no mobile horizontal overflow. |
| Mobile overflow fix | Vite build plus Playwright viewport `390x844` | Passed after adding `min-w-0` to statistics filter labels and controls; `body.scrollWidth` equals viewport width. |

## Follow-up UI Fixes - 2026-09-12

- Added `cursor-pointer` to voting and statistics action buttons.
- Restored the required native model selector backed by the complete `/statistics/models` response; the imported dataset exposes 74 options.
- Added server-side statistics result pagination: 24 cars per page by default, validated maximum 100, standard pagination metadata, and a separate total-vote query for the complete filtered set.
- Replaced the date fields with year-only selectors from 1886 through the current year; backend filtering remains year-based.
- Fixed ezPlus duplicate initialization and horizontal overflow: both images initialize once per pair, stale containers and handlers are removed, tablet/mobile use a contained lens, desktop zoom windows open inward, and zoom image styles preserve source proportions. A photo-loading error now disables voting and is shown to the user.
- Added a 30-per-minute IP rate limiter to the public vote write route and a regression test for HTTP 429.
- Final focused HTTP suite: 13 tests, 93 assertions. Full suite: 36 tests, 157 assertions. Vite production build, Pint, and Composer audit passed.
- Browser smoke on 1,497 imported cars: 24 result rows/cards loaded per page, page two loaded without navigation, all 74 models remained selectable, and zoom hover kept document width equal to the viewport on mobile and desktop.
