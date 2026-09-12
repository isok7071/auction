# AI Workflow

This document records how AI-assisted engineering was used on the Fordewind project.

## Workflow Summary

- Planning: Requirements from `Task.md` were converted into `.agents/plans/fordewind-laravel-design.md` and copied into `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md`.
- Implementation: Laravel 13 was bootstrapped, the vendor-agnostic harness is complete, and Task 2 Docker Compose is implemented with PHP-FPM, nginx, MySQL, and a node/Vite service. Product features now proceed to data model and import.
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

No subagents have been used yet.

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

## Verification Evidence

| Check | Command | Result |
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
- Import logic, voting backend, voting UI, and statistics UI are not implemented yet.
- Laravel Cloud CLI was not installed globally because deployment is outside the current local setup stage.
- `.mcp.json` and `opencode.json` are local/gitignored. Portable mappings are `.mcp.json.example` and `opencode.json.example`.
- MCP servers configured: `laravel-boost`, `fetch` (Docker `mcp/fetch`), `browser`, `thinking`, `memory`. No separate `context` process; Boost covers Laravel context.
- Codex project MCP lives in committed `.codex/config.toml` and loads only when Codex trusts this project.
- Because there is no initial commit, `git status --short` is more useful than `git diff` for the current tree.

## Human Review Notes

- Do not reinstall PHP with `php.new`.
- Docker delivery versions are now recorded in D017: PHP 8.5, Node 24 LTS, MySQL 8.4 LTS, and stable nginx 1.30.
- `AGENTS.md` is the operating manual. A pasted vendor-switch prompt is not required.
- Harness and Docker Compose are complete. Next vendor starts data model and import, then product features.
- Every detail needed for a vendor switch must live in project files.
- README and product/business documentation must be Russian; agent harness stays English.
