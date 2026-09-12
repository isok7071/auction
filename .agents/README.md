# Agent Workspace

This directory contains vendor-agnostic guidance for AI-assisted development of the Fordewind car photo voting application.

The project uses AI agents as engineering assistants, not as an unchecked code generator. Agents must preserve Laravel conventions, keep architecture proportional to the product, document decisions, and verify generated code with tests and runtime checks.

## Contents

- `skills/fordewind-laravel/SKILL.md` - project coding standards and architecture boundaries.
- `skills/fordewind-harness/SKILL.md` - planning, MCP, subagent, documentation, and verification workflow.
- `skills/*` - Boost, Fordewind, and Superpowers skills. Superpowers is vendored from https://github.com/obra/superpowers so another vendor does not need a Cursor plugin.
- `superpowers/` - upstream license and version note for the vendored Superpowers skills.
- `mcp/README.md` - how MCP servers should be configured safely.
- `mcp/servers.example.json` - portable MCP server configuration template without secrets.
- `templates/AGENTS.md` - root agent guidance template reconciled with framework-generated guidance after Laravel is installed.
- `templates/AI_WORKFLOW.md` - workflow artifact template for documenting AI usage.
- `templates/FEATURE_CHECKLIST.md` - requirement coverage checklist for implementation and review.
- `templates/HANDOFF.md` - vendor-switch handoff template.

Live artifacts written from these templates live in `docs/agents`. `AGENTS.md` is the operating manual. `docs/agents/HANDOFF.md` is the live stage pointer. MCP and skills inventory: `docs/agents/TOOLING.md`. Harness is complete; the next vendor starts Docker.

## Rules

- Do not store secrets in this directory.
- Keep guidance project-focused: domain, architecture, coding standards, verification.
- Do not describe the project as an interview assignment in agent rules.
- Prefer portable MCP and skill documentation over vendor-specific configuration.
- If a tool-specific config is needed, derive it from these templates and document the mapping.
