# Handoff

`AGENTS.md` is the operating manual. This file is the live stage pointer, including which implementation plan to follow. Do not rely on chat history.

## Current Stage

**Harness and Docker are complete.** Laravel 13 is bootstrapped. MCP, skills, handoff files, and Docker Compose runtime files are in the repo.

The next vendor starts **Task 3: Data Model And Import**. Product voting/statistics features wait until the data model and import are implemented.

## Next Step

1. Read `docs/agents/TOOLING.md` for the MCP and skills inventory.
2. Follow `docs/superpowers/plans/2026-09-11-fordewind-implementation.md` from **Task 3**.
3. Implement migrations, Eloquent models/factories, database-backed sessions if needed, and the idempotent import path.
4. Use the Docker MySQL settings from `.env.example`; do not treat the old local SQLite `.env` as the delivery runtime.
5. Record commands, verification, and decisions in `docs/agents/DECISIONS.md` and `docs/agents/AI_WORKFLOW.md`.

Do not reinstall host PHP with `php.new`. Do not treat earlier design notes that mentioned PHP 8.4 as a lock.

## Scope Completed In This Stage

- Laravel skeleton merged into the existing project root without deleting `Task.md` or `.agents`.
- Laravel Boost installed and merged into `AGENTS.md`.
- Vendor-agnostic skills, MCP templates, decisions, workflow log, checklist, spec, plan, and this handoff file.
- Superpowers, Fordewind, and Boost skills vendored into `.agents/skills` and linked from `.codex/skills`.
- README in Russian for people; agent files in English.
- Docker Compose added with `app` PHP-FPM, `nginx`, `mysql`, and profiled `node` service for Vite/npm commands.
- Docker uses PHP 8.5 Alpine, nginx 1.30 Alpine, MySQL 8.4 LTS, and Node 24 LTS Debian slim.
- `.env.example` is the shared Laravel/Vite environment source and points at Docker MySQL.
- nginx serves built Vite assets; the profiled node service exposes direct Vite HMR/WebSocket access.

## Read First

Agents already have `AGENTS.md` for how to work. This file is the live stage, including the current implementation plan.

How to work:

1. `AGENTS.md`
2. `docs/agents/TOOLING.md`
3. `docs/agents/DECISIONS.md`
4. `docs/agents/AI_WORKFLOW.md`

Current work (not operating rules):

5. `Task.md`
6. `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md`
7. `docs/superpowers/plans/2026-09-11-fordewind-implementation.md` from Task 3
8. `docs/agents/FEATURE_CHECKLIST.md`

## Files Changed In Bootstrap And Harness

Created or substantially written:

- Laravel 13 application scaffold in the project root.
- `docker-compose.yml`, `docker/php/Dockerfile`, `docker/php/php.ini`, `docker/nginx/default.conf`, `.dockerignore`.
- `AGENTS.md`, `CLAUDE.md`, `boost.json`, `README.md`.
- `docs/agents/*` including `TOOLING.md`.
- `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md`
- `docs/superpowers/plans/2026-09-11-fordewind-implementation.md`
- `.mcp.json.example`, `.codex/config.toml`, `opencode.json.example`.
- `.agents/skills/*` including Boost, Fordewind, and Superpowers skills. Codex links live in `.codex/skills`.

Preserved:

- `Task.md`
- `.agents/plans/fordewind-laravel-design.md`
- `.agents/templates/*`
- `.agents/skills/fordewind-laravel/SKILL.md`
- `.agents/skills/fordewind-harness/SKILL.md`

Local / not source-of-truth:

- `.env` - generated locally, gitignored, currently SQLite defaults.
- `.mcp.json` and `.cursor/mcp.json` - local Cursor MCP copies, gitignored.
- `.cursor/` - vendor-local.
- `.agents/mcp/cursor-memory/project.jsonl` - memory graph, gitignored.

Committed Codex MCP config is `.codex/config.toml`. Recreate Cursor MCP with `cp .mcp.json.example .mcp.json`.

## Commands Run

See `docs/agents/AI_WORKFLOW.md` for full evidence. Summary:

- `composer create-project laravel/laravel .laravel-bootstrap --no-interaction`
- `composer require laravel/boost --dev --no-interaction`
- `php artisan boost:install --no-interaction`
- `npm install --ignore-scripts`
- `npm run build`
- `php artisan test --compact`
- `docker compose config`
- `docker compose build app`
- `docker compose run --rm --no-deps app php artisan about`

## Verification

| Check | Result |
| --- | --- |
| Laravel boots | `php artisan about` → Laravel 13.31.0, env `local` |
| Composer metadata | `composer validate --strict` passed |
| Default tests | `php artisan test --compact` → 2 passed |
| Vite scaffold build | `npm run build` passed with full network |
| Docker config | `docker compose config` passed |
| Docker app image | `docker compose build app` → `fordewind-app:php-8.5`, about 138 MB |
| Docker Laravel smoke | `php artisan about` in app container → Laravel 13.31.0, PHP 8.5.10, DB `mysql` |
| Vite HMR | `/@vite/client` → HTTP 200; WebSocket with `vite-hmr` protocol opened |
| nginx assets | nginx 1.30.4; `/build/manifest.json` → HTTP 200 with immutable cache header |
| Git | Initialized on `main`, with no commits. Do not create commits unless the developer asks. |

## Risks And Constraints

- Host PHP is 8.3.17 and cannot run the project dependencies after the delivery constraint changed to `php: ^8.5`; use the app container.
- Docker versions chosen in D017: PHP 8.5 FPM Alpine, MySQL 8.4 LTS, nginx 1.30 Alpine, Node 24 LTS Debian slim.
- Host PHP has no SQLite driver. Runtime database is Docker MySQL.
- Local `.env` may still contain bootstrap SQLite values. Copy `.env.example` for Docker runtime setup.
- `php.new` was rejected by the developer.
- Do not mix jQuery and Vue in one functional area.
- Do not read `env()` outside config files.
- Human-facing README and product spec stay Russian. Agent harness files stay English.
- Git has no commits yet.

## Developer Review Notes

- Do not reinstall PHP. Use the existing PHP/Composer toolchain.
- Harness and Docker are done. The next vendor implements import/data model, then backend and frontends.
- Fill every detail needed for a vendor switch into project files, not chat memory.
