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

## D018 - Backend Contract Is Based On The Inspected Local Source Snapshot

**Decision:** The backend execution plan imports a local source directory only, identifies cars by `AuctionItemId`, groups selectable models by exact `Make + ' ' + Model`, copies images to the public disk, keeps a per-session/model shuffled deck, and counts received votes from `winner_car_id`.

**Rationale:** The inspected source snapshot contains 1,497 JSON and 1,497 matching JPG files; its JSON has both `Make` and `Model`, so a single bare model field is not a stable selector. A local command satisfies `Task.md` without an unneeded remote-fetch credential/update mechanism. Web JSON routes preserve the existing database Laravel session and CSRF protection.

**Consequence:** Execute `.agents/plans/fordewind-laravel-design.md` before frontend work. The later jQuery/Vue implementations consume its four endpoint contracts rather than inventing separate state or aggregate rules.

## D019 - Preserve The Agreed Domain, Infrastructure, And Repository Boundaries

**Decision:** The Fordewind backend uses `app/Domain` for models, contracts, and immutable DTOs; `app/Infrastructure` for Eloquent repositories and storage adapters; and `app/Services` for application orchestration. `AppServiceProvider` binds each domain contract to its infrastructure implementation.

**Rationale:** This is the architecture agreed for the project. Laravel's direct Eloquent use would also be valid in a smaller application, but it would erase the agreed boundaries and make imports, aggregate queries, and filesystem persistence harder to replace or isolate in tests.

**Consequence:** D016's preference for avoiding premature Domain/Infrastructure folders is superseded for this backend scope. DTOs are used for compound values crossing layers, not for individual scalar parameters or to duplicate Eloquent models.

## D020 - Keep Cars Schema Normalized Without Raw Source Payload

**Decision:** `cars` stores normalized fields required by filtering, display, identity, and voting. It does not store the complete source JSON payload.

**Rationale:** `Task.md` requires importing and displaying needed source data, not preserving an opaque copy. A raw JSON column would increase row size and duplicate data without a current consumer. The schema has not been released, so correcting its initial migration keeps fresh installations clean.

**Consequence:** Import DTO maps only normalized fields. Because the schema has not been released, the initial `cars` migration was corrected directly and the local database rebuilt with `migrate:fresh`. Future source fields require an explicit schema decision and migration.

## D021 - Backend Naming, Model Metadata, And Query Safety

**Decision:** Cross-layer data objects use the `Dto` suffix. Eloquent models expose constants for persisted field names and document attributes in class PHPDoc. Application PHP uses those constants; migrations remain literal schema snapshots. Lazy loading is prevented outside production.

**Rationale:** Explicit DTO naming makes layer boundaries visible. Central field names reduce typo-prone duplication in repositories, factories, and tests. Lazy-loading prevention catches N+1 regressions during development and testing.

**Consequence:** New repositories keep Eloquent details under `app/Infrastructure/Persistence/Eloquent`, storage adapters under `app/Infrastructure/Storage`, contracts and DTOs under their owning `app/Domain`, and orchestration under `app/Services`. Relationships needed by collections or resources must be eager-loaded.

## D022 - Preserve Every Photo At The Voting-Cycle Boundary

**Decision:** The session deck retains a single final unseen photo when a model has an odd number of photos. Its boundary pair combines that final photo with one freshly shuffled repeat, then stores the remaining new deck. The pair and vote routes use Laravel session blocking with `->block(10, 10)`.

**Rationale:** Dropping the final singleton would let a cycle reset without showing every available photo, which contradicts the product rule. Session route blocking serializes rapid same-browser AJAX calls before they consume or clear the pending pair.

**Consequence:** `VotingPairService` owns `clearPendingPair()` in addition to pair issuance. `VoteService` clears the pending pair only after its repository transaction creates the vote. Tests cover the five-photo boundary and sequential duplicate vote rejection.

## D023 - Session-Protected Voting API And Model-Key Scopes

**Decision:** Voting uses named `web.php` JSON routes, Laravel session blocking on pair/vote mutations, and a pending-pair state keyed by the selected `Make + Model` value. Statistics uses `winner_car_id` as the received-vote aggregate and returns the total once under response `meta`. `Car` owns MySQL model-key query scopes.

**Rationale:** The client must not be able to vote for arbitrary photos or consume concurrent pairs. The delivery runtime and verification environment are MySQL, so the model-key expression follows MySQL semantics.

**Consequence:** `VotingPairService` and `VoteService` remain server-side authorities for a pair and vote. `EloquentVotingRepository` counts joined `car_photos` across each grouped model, while `EloquentVotingRepository` and `EloquentStatisticsRepository` apply `Car::selectModelKey()` and `Car::whereModelKey()` instead of duplicating the MySQL `CONCAT()` expression. Frontend code consumes four stable endpoints without embedding selection or aggregation rules.

## D024 - Separate Human Pages From Statistics Data Routes

**Decision:** `/statistics` renders the Blade page, while `/statistics/data` returns filtered rows and `/statistics/models` returns every model available to the statistics filter. The voting selector remains a separate `/voting/models` contract.

**Rationale:** One URL cannot reliably serve both the human page and its JSON payload. Voting requires at least two photos, while statistics must include models and cars even when they cannot produce a voting pair.

**Consequence:** Blade only supplies relative endpoint URLs to the frontend mount. Vue and jQuery request JSON explicitly with `Accept: application/json`; neither UI duplicates backend selection rules.

## D025 - Vue Statistics Uses Composition API And SFC Boundaries

**Decision:** The statistics frontend uses Vue 3 `<script setup>` single-file components. `statistics.js` only mounts `StatisticsApp.vue`; HTTP parsing lives in `statistics/api`, request and filter state in `statistics/composables`, and focused presentation components in `statistics/components`.

**Rationale:** Keeping the template, transport, concurrency handling, formatting, and all state in one entry file makes changes hard to review and test. These boundaries keep orchestration visible without introducing a UI framework or state-store dependency.

**Consequence:** New statistics behavior extends the relevant component, composable, or API module. jQuery and ezPlus remain isolated to the voting entrypoint.

## D026 - Paginated Statistics Results And Complete Model Selector

**Decision:** `/statistics/models` returns every distinct model for a native Vue selector. `/statistics/data` paginates filtered cars on the server with 24 rows by default and a validated maximum of 100, while `meta.total_votes` covers the complete filtered result set.

**Rationale:** The product requirement calls for model selection, and the imported dataset has only a modest number of distinct model keys. The actual performance problem was rendering all 1,497 car results and photos at once, so pagination belongs on the result collection rather than the selector.

**Consequence:** The selector remains complete and predictable. Vue requests one result page at a time, page changes do not navigate the browser, and the API exposes standard pagination metadata in addition to the filtered vote total.

## D027 - Responsive Zoom And Vote Rate Limit

**Decision:** Voting renders its two-card layout and external ezPlus zoom only from the `lg` breakpoint. Below it, a contained lens keeps the tint interaction inside the photo. Vote submission is limited to 30 requests per minute per IP address.

**Rationale:** A two-column layout combined with an external zoom window at tablet widths can exceed the viewport. The voting endpoint is intentionally public, but an unbounded write endpoint permits avoidable automated vote inflation.

**Consequence:** The jQuery page remains the sole owner of ezPlus and removes its handlers before a new pair is rendered. Normal voting remains asynchronous; excessive write requests receive HTTP 429.
