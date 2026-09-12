# MCP And Skills

Canonical inventory for this repository. A new vendor should use these tools, not a personal Cursor plugin list.

## MCP servers

Configured in `.agents/mcp/servers.example.json`, `.mcp.json.example`, and `.codex/config.toml`.

| Server | When to use | Command |
| --- | --- | --- |
| `laravel-boost` | Laravel docs, schema, routes, Boost tools. This is the Laravel context server. | `php artisan boost:mcp` |
| `fetch` | Read public docs and web pages. | `docker run -i --rm mcp/fetch` |
| `browser` | UI smoke for voting and statistics pages. | `npx -y @playwright/mcp@latest` |
| `thinking` | Multi-step design, debugging, trade-offs. | `npx -y @modelcontextprotocol/server-sequential-thinking` |
| `memory` | Durable project notes. File is gitignored. | `npx -y @itseasy21/mcp-knowledge-graph` |

There is no separate `context` server. Boost covers Laravel; `fetch` covers other URLs.

Do not treat user-global servers such as Kaiten or Grafana as part of this project.

## Skills

All skills live in `.agents/skills`. Codex must also have the same set as symlinks in `.codex/skills` (Fordewind, Boost, and Superpowers).

### Fordewind

| Skill | When to use |
| --- | --- |
| `fordewind-laravel` | Backend, import, voting, statistics, Docker, tests, Laravel conventions for this app. |
| `fordewind-harness` | Stages, MCP, handoff, decisions, verification, documentation. |

### Laravel Boost

| Skill | When to use |
| --- | --- |
| `laravel-best-practices` | Writing or reviewing Laravel PHP code. |
| `testing-best-practices` | PHPUnit tests, coverage, naming, isolation. |
| `tailwindcss-development` | Tailwind layout and styling. |
| `deploying-to-cloud` | Only if deploying to Laravel Cloud. Not needed for local Docker. |
| `infer-conventions` | Only when the developer explicitly asks to record `.ai/rules`. |

### Superpowers (vendored from https://github.com/obra/superpowers v6.3.0)

| Skill | When to use |
| --- | --- |
| `using-superpowers` | Start of a session: find and invoke the right skill first. |
| `brainstorming` | New behavior or design before implementation. |
| `writing-plans` | After an approved spec, before coding. |
| `executing-plans` | Follow a written plan in this session. |
| `subagent-driven-development` | Execute a plan with one subagent per task, if the harness supports it. |
| `dispatching-parallel-agents` | Independent research or review tasks in parallel. |
| `test-driven-development` | Feature and bugfix implementation: failing test first. |
| `systematic-debugging` | Any unexpected failure before guessing a fix. |
| `verification-before-completion` | Before claiming done, passing, or fixed. |
| `using-git-worktrees` | Isolated feature work when git worktrees are in use. |
| `requesting-code-review` | After a major feature or before merge. |
| `receiving-code-review` | When review comments arrive. |
| `finishing-a-development-branch` | After implementation is complete and tests pass. |
| `writing-skills` | Only when creating or editing a skill. |

## Next vendor defaults

`AGENTS.md` is already the operating manual. Do not wait for a pasted prompt.

For Docker and later product work, start with:

1. `using-superpowers`
2. `fordewind-harness`
3. `fordewind-laravel`
4. `laravel-best-practices`
5. `test-driven-development` once product code starts
6. `systematic-debugging` if a command or container fails
7. MCP: `laravel-boost` for Laravel APIs; `fetch` for current Docker/PHP docs
