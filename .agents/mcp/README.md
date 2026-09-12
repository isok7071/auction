# MCP Configuration

MCP servers extend AI agents with live tools. This project keeps MCP guidance portable so it can be adapted to different agent clients.

The canonical project inventory of MCP servers and skills is `docs/agents/TOOLING.md`.

## Defaults

- Start with read-only tools whenever possible.
- Keep credentials in environment variables or a local secret store.
- Do not commit tokens, API keys, cookies, generated session files, personal machine paths, or host-specific wrappers such as `wsl.exe` plus an absolute PHP path.
- Record MCP usage in `docs/agents/AI_WORKFLOW.md`: server, task, reason, and result.
- Verify every server after configuration with one harmless read-only request.

## Enabled Servers

| Server | Role | Command |
| --- | --- | --- |
| `laravel-boost` | Laravel docs, schema, browser logs, project tools. This is the documentation/context server for Laravel. | `php artisan boost:mcp` |
| `fetch` | Fetch public web/docs pages. | `docker run -i --rm mcp/fetch` |
| `browser` | UI smoke checks. | `npx -y @playwright/mcp@latest` |
| `thinking` | Sequential thinking for multi-step analysis. | `npx -y @modelcontextprotocol/server-sequential-thinking` |
| `memory` | Durable project notes in a local knowledge graph. | `npx -y @itseasy21/mcp-knowledge-graph` |

`fetch` uses Docker because `uvx` is not installed here and the npm package `mcp-server-fetch` is a security stub. The original template command `uvx mcp-server-fetch` is still valid on machines that have `uv`.

There is no separate `context` process. Laravel Boost covers Laravel/package context; `fetch` covers arbitrary URLs.

Memory file: `.agents/mcp/cursor-memory/project.jsonl` (gitignored). Do not commit that file.

## Source Template

Treat these files as the committed source of truth:

- `.agents/mcp/servers.example.json` - full portable server set.
- `.mcp.json.example` - mapping for clients that read `.mcp.json`.
- `.codex/config.toml` - project-scoped Codex MCP. Codex loads it only when the project is trusted.
- `opencode.json.example` - OpenCode mapping.

Local generated copies (gitignored):

- `.mcp.json`
- `.cursor/mcp.json`
- `opencode.json`
- other files under `.codex/` except `config.toml`

To recreate the local Cursor/Claude mapping:

```bash
cp .mcp.json.example .mcp.json
```

Codex does not need a copy step. It reads `.codex/config.toml` in this repository after the project is trusted.

## Client Mapping

| Client | Typical config path | Notes |
| --- | --- | --- |
| Cursor | `.cursor/mcp.json` or `.mcp.json` | `.cursor/` is gitignored. Reload MCP after changing these files. |
| Claude Code | `.mcp.json` | Copy from `.mcp.json.example`. |
| Codex | `.codex/config.toml` | Committed, portable, no host paths. Codex loads it only for a trusted project. `/mcp` may not list project servers even when the agent can use them. |
| OpenCode | `opencode.json` | Gitignored. Copy from `opencode.json.example`. |

When mapping to a client, keep the committed file secret-free. Put private values in `.env`, shell environment, or the client secret manager.
