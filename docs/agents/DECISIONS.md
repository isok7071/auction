# Decisions

This file records architecture and tooling decisions for the Fordewind Laravel application.

## D001 - Bootstrap Laravel In The Existing Project Root

**Decision:** Laravel was scaffolded with `composer create-project laravel/laravel .laravel-bootstrap --no-interaction`, then merged into the repository root while preserving `Task.md` and `.agents`.

**Rationale:** The project root already contained task and agent artifacts, so installing directly into `.` would risk overwriting existing files. A temporary skeleton made the merge explicit and recoverable.

**Evidence:** Composer installed `laravel/laravel v13.10.1` with `laravel/framework v13.31.0`.

## D002 - Do Not Reinstall System PHP

**Decision:** The `php.new` installer was not used after the developer asked why PHP was being installed.

**Rationale:** The host already has PHP 8.3 and Composer. Only the Laravel CLI was missing, and the project can be scaffolded safely with Composer without changing the system PHP toolchain.

**Consequence:** The local shell remains on PHP 8.3. Docker runtime versions are chosen later; they are not pinned here.

## D003 - Use Laravel Boost Plus Vendor-Agnostic Harness

**Decision:** Laravel Boost was installed as a dev dependency and root `AGENTS.md` was extended with Fordewind-specific guidance.

**Rationale:** Boost provides version-aware Laravel guidance, while the assignment also requires portable AI workflow artifacts that another agent/vendor can use without chat history.

**Consequence:** `AGENTS.md` contains both Boost-generated rules and Fordewind project rules. Stable handoff artifacts live in `docs/agents`.

## D004 - Keep Cursor-Specific Boost Files Optional

**Decision:** The failed Cursor-specific Boost writes to `.cursor/skills` and `.cursor/mcp.json` were not forced.

**Rationale:** `.cursor` is ignored and read-only in this environment. The project must remain vendor-agnostic, so Cursor-specific runtime configuration is optional.

**Consequence:** Boost successfully generated shared guidance, while MCP examples remain as portable templates under `.agents/mcp`.

## D005 - Defer Global Laravel Cloud CLI

**Decision:** The global Laravel Cloud CLI was not installed during this local bootstrap stage.

**Rationale:** It modifies the developer's global Composer environment and is only needed for deployment. The current task is local implementation and harness setup.

**Consequence:** If deployment becomes part of the scope, install it explicitly and record the command and result here.

## D006 - Portable MCP Templates, Local Client Configs

**Decision:** Committed MCP source of truth is `.agents/mcp/servers.example.json`, `.mcp.json.example`, `.codex/config.toml`, and `opencode.json.example`. Generated client files such as `.mcp.json` and `opencode.json` are gitignored.

**Rationale:** Boost wrote host-specific `wsl.exe` and absolute PHP paths. Those work on one machine and leak local layout if committed. Another vendor must be able to copy a portable template. Codex is the exception: its project config is committed and portable.

**Consequence:** Local Boost MCP still works from the generated `.mcp.json`. A new Cursor/Claude vendor copies `.mcp.json.example`. Codex reads committed `.codex/config.toml` when the project is trusted.

## D007 - Complete Harness Before Infrastructure

**Decision:** The harness stage is complete. The next vendor starts Docker Compose and MySQL. Import and product features wait until Docker is up.

**Rationale:** The developer is switching vendors and asked to freeze the harness, then continue with Docker on the next agent.

**Consequence:** Follow `AGENTS.md`, then the live stage in `docs/agents/HANDOFF.md`, then Task 2 in `docs/superpowers/plans/2026-09-11-fordewind-implementation.md`. Do not reinstall PHP with `php.new`.

## D008 - Keep Boost Skills In `.agents/skills`

**Decision:** Boost skills are retained under `.agents/skills` in addition to any vendor-specific copies such as `.claude/skills`.

**Rationale:** Cursor-specific Boost writes failed. `AGENTS.md` already tells agents to load skills from `**/skills/**`. Portable skills must survive a vendor switch.

**Consequence:** Do not depend on `.cursor/skills` existing. Use `.agents/skills` as the shared skill directory.

## D010 - Enable Fetch, Browser, Thinking, Memory, And Boost MCP

**Decision:** The project MCP set is `laravel-boost`, `fetch`, `browser`, `thinking`, and `memory`. There is no separate `context` server.

**Rationale:** Those were the servers named in `.agents/mcp` templates. `thinking` and `memory` came from the local draft that used `@modelcontextprotocol/server-sequential-thinking` and `@itseasy21/mcp-knowledge-graph`. Laravel Boost already provides Laravel documentation context, so a dummy `context` command was not added. `fetch` runs through Docker `mcp/fetch` because `uvx` is missing and npm `mcp-server-fetch` is a security stub.

**Consequence:** Committed templates stay portable and secret-free. Local `.mcp.json` and `.cursor/mcp.json` are gitignored. Memory lives in gitignored `.agents/mcp/cursor-memory/project.jsonl`. Reload the MCP client after changing these files.

## D011 - Commit Project-Scoped Codex MCP

**Decision:** Codex MCP is configured in committed `.codex/config.toml`. Other files under `.codex/` stay gitignored.

**Rationale:** Codex reads project `.codex/config.toml` when the project is trusted. That is the supported per-project hook. Boost's generated file used `wsl.exe` and absolute PHP paths, which are not portable.

**Consequence:** A later Codex vendor gets the same server set (`laravel-boost`, `fetch`, `browser`, `thinking`, `memory`) without copying a template. The project must be trusted in Codex. `/mcp` may not list these servers; the agent can still use them.

## D012 - Vendor Superpowers Skills In The Repository

**Decision:** Superpowers skills from https://github.com/obra/superpowers v6.3.0 are copied into `.agents/skills`. Codex loads the same folders through `.codex/skills` symlinks.

**Rationale:** Other vendors do not have the Cursor Superpowers plugin. Checking the skills into the repo makes brainstorming, planning, TDD, and review workflows available without a per-machine plugin install.

**Consequence:** Update by replacing the skill folders from a newer upstream `skills/` tree. Superpowers is not a Composer or npm dependency. License: MIT, see `.agents/superpowers/LICENSE`.

## D009 - Russian For People, English For Agents

**Decision:** Human-facing product docs are Russian. Agent harness files stay English unless a file is explicitly meant for developers.

**Rationale:** Laravel Boost, skills, and most agent runtimes follow English instructions more reliably. The team and assignment reviewers read Russian. Mixing those audiences in one file makes both worse.

**Consequence:** Write `README.md` and product/business specs in Russian. Keep `AGENTS.md`, skills, `HANDOFF.md`, `TOOLING.md`, and other agent instructions in English. Code identifiers stay English.

## D013 - Canonical MCP And Skills Inventory

**Decision:** Project MCP and skills are listed in `docs/agents/TOOLING.md`. Human README repeats the same inventory in Russian.

**Rationale:** The next vendor must know which servers and skills belong to this repo, not a personal Cursor plugin list. Kaiten and Grafana stay user-global and out of project scope.

**Consequence:** Do not add undocumented MCP servers or skip the listed project skills when they apply. Update `TOOLING.md` if the set changes.

## D014 - Do Not Pin Docker Image Versions In Handoff

**Decision:** Handoff and the next Docker task do not lock PHP 8.4, MySQL 8, or other concrete image versions. The next agent picks versions from `composer.json`, current Laravel docs, and what the stack needs, then records the choice.

**Rationale:** `Task.md` asks for current stable Laravel, MySQL, and Docker Compose. It does not name PHP 8.4. Installed Laravel 13 requires `php: ^8.3`. Earlier design notes that mentioned 8.4 were a draft, not an approved pin.

**Consequence:** Treat `.agents/plans/fordewind-laravel-design.md` version numbers as historical. Do not install host PHP via `php.new`.

## D015 - AGENTS.md Is The Operating Manual

**Decision:** Agents learn how to work in this repository from `AGENTS.md`. `docs/agents/HANDOFF.md` is only the live stage pointer. A pasted vendor-switch prompt is not required.

**Rationale:** Clients already inject `AGENTS.md` (and Claude injects `CLAUDE.md`, which now points at `AGENTS.md`). A long first message duplicates that file and will drift.

**Consequence:** Keep the session protocol at the top of `AGENTS.md`. Do not put implementation plans or feature spec paths in `AGENTS.md`. Those are current work; `HANDOFF.md` points to them. Update `HANDOFF.md` when the live stage changes.

## D016 - Execution Plan Over Historical Design Notes

**Decision:** The next agent executes `docs/superpowers/plans/2026-09-11-fordewind-implementation.md` from Task 2. `.agents/plans/fordewind-laravel-design.md` is historical. Prefer Laravel conventions before `app/Domain` or `app/Infrastructure`. Codex skills in `.codex/skills` must include Fordewind and Boost, not only Superpowers.

**Rationale:** An audit found the historical design file still said Laravel was not installed, the plan required subagents, Codex was missing project skills, and some files still said to start at `HANDOFF.md`.

**Consequence:** If those files disagree, follow `AGENTS.md`, then the implementation plan, then the spec. Do not reinstall Laravel.

## D017 - Docker Runtime Versions And Service Split

**Decision:** Docker Compose uses a custom `php:8.5-fpm-alpine` app image, `nginx:1.30-alpine`, `mysql:8.4`, and `node:24-bookworm-slim` for Vite/npm commands.

**Rationale:** PHP 8.5 is the current stable actively supported PHP branch and Laravel 13 supports it. Node 24 is the current LTS line; Node 26 is Current rather than LTS and is not the production default. MySQL 8.4 is the LTS line and nginx 1.30 is the stable line. PHP build dependencies are removed in the same Alpine layer, reducing the app image to about 138 MB. Node uses Debian slim because the repository's host-installed native frontend bindings target glibc and fail inside Alpine/musl.

**Consequence:** Runtime services are `app`, `nginx`, and `mysql`; the `node` service is available under the `assets` profile. `app` and `node` consume `${APP_ENV_FILE:-.env}`. Vite HMR is exposed directly on port 5173, while nginx serves Laravel and production assets from `public/build`.
