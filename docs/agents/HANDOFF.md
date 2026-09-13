# Handoff

`AGENTS.md` is the operating manual. This file describes only the live delivery state.

## Current Stage

Backend and frontend implementation are complete in the working tree. The voting page at `/` uses jQuery/AJAX and responsive ezPlus Tints; the statistics page at `/statistics` uses Vue 3 Composition API with single-file components, a composable, a separate API module, and server-side pagination.

The current uncommitted review follow-up centralizes the PHP model key, names the statistics year pattern, prevents voting before both photos load, and documents deferred index work in README.

Automated verification, production asset compilation, database migration, full source import, HTTP smoke, and interactive browser smoke passed.

## Next Allowed Step

1. Keep the working tree for developer review or create a commit only after explicit developer approval.
2. Restart `node` for Vite HMR when needed; stop it and remove generated `public/hot` for production-asset browser smoke.

## Implemented Contracts

- Pages: `GET /` (`voting.page`), `GET /statistics` (`statistics.page`).
- Voting JSON: `GET /voting/models`, `GET /voting/pair`, `POST /voting/votes`.
- Statistics JSON: `GET /statistics/models`, `GET /statistics/data`.
- Voting frontend: `resources/js/voting.js` owns jQuery state, AJAX, CSRF, pair rendering, and ezPlus initialization.
- Statistics frontend: `resources/js/statistics.js` mounts `StatisticsApp.vue`; `api/statisticsApi.js`, `composables/useStatistics.js`, and focused SFC components own their respective concerns.

## Verification Evidence

- Focused frontend/statistics feature suite after pagination: 10 passed, 49 assertions.
- Complete project suite after final UI, rate-limit, and documentation fixes: 36 passed, 157 assertions.
- Vite 8 production build with Vue plugin: passed and emitted separate voting/statistics bundles.
- Pint dirty formatting: passed at the final gate.
- MySQL migration: nothing pending.
- Full local import: command reported 1,497 created cars and 1,497 copied photos; idempotency is covered by the import feature test.
- HTTP smoke: `/`, `/statistics`, `/statistics/models`, and `/statistics/data` returned 200.
- Browser MCP still expects a system Chrome executable, but final visual smoke passed through a temporary Playwright Chromium runtime without changing project dependencies.
- Browser voting smoke: model selection, pair rendering, ezPlus initialization, vote submission, next pair, and no page reload passed.
- Browser statistics smoke: native model selector with all 74 imported models, model/year filter, client validation, 24 results per page, next-page loading without navigation, mobile cards, and no horizontal overflow passed.
- Responsive voting regression test: the server-rendered card grid switches to two columns at `lg`; below that breakpoint jQuery selects a contained ezPlus lens.
- Final Playwright voting smoke: at 911px and 390px, including active hover on the ezPlus overlay, `body.scrollWidth` and `documentElement.scrollWidth` equalled the viewport. The cards remained one column and the lens was visible.
- Final Playwright statistics smoke: 74 model options, 142 values in each year-only selector, and 24 rendered table rows; page width equalled the 1280px viewport.
- Blade-cache permission recovery: root-owned compiled views caused `touch(): Utime failed`; clearing and recompiling as `www-data` restored both pages to HTTP 200. The README records the recovery command and runs PHPUnit as `www-data`.
- Vote rate-limit regression test: 31st request from the same IP receives HTTP 429 after 30 requests per minute.
- Composer audit: no security vulnerability advisories found.
- Current review follow-up: focused PHP suite passed (30 tests, 126 assertions); Vite production build passed after the voting lifecycle change.
- Test review follow-up: full PHPUnit suite passed with 36 tests and 158 assertions; skeleton tests and a source-level CSS assertion were removed.
- Clean Docker-install recovery: after preparing fresh named volumes with `docker compose run --rm --no-deps app true`, `composer install` under the host UID completed `package:discover` successfully.

## Risks And Constraints

- Browser MCP itself requires a system Chrome executable. A temporary Playwright Chromium runtime is sufficient for local headless smoke when Chrome is unavailable.
- Host PHP does not match the project runtime; use the PHP 8.5 Docker app container.
- Runtime database is Docker MySQL. Do not run `migrate:fresh`, seeders, or other destructive database commands.
- Source data under `storage/app/import-source`, generated public images, `.env`, credentials, and tokens must remain untracked.
- Keep voting jQuery-only and statistics Vue-only.

## Read First

1. `AGENTS.md`
2. `docs/agents/TOOLING.md`
3. `Task.md`
4. `.agents/plans/fordewind-laravel-design.md` at Task 10
5. `docs/agents/DECISIONS.md`
6. `docs/agents/AI_WORKFLOW.md`
7. `docs/agents/FEATURE_CHECKLIST.md`
