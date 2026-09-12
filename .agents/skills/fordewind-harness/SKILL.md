---
name: fordewind-harness
description: Guides AI-assisted planning, MCP usage, subagent delegation, decision logging, documentation, and verification for the Fordewind car photo voting application. Use when starting work, decomposing tasks, using agents/tools, or preparing delivery artifacts.
---

# Fordewind Harness

## Operating Principles

- Work in explicit stages: context, design, plan, implementation, verification, documentation.
- Keep the workflow vendor-agnostic. Do not assume Cursor, Claude, Codex, or any single agent runtime.
- Use MCP tools when they provide fresher or more reliable context than memory.
- Use subagents for independent read-only research or review tasks.
- Do not let generated code pass without verification.
- Record important decisions and evidence as project artifacts.

## Required Artifacts

Maintain these files during development:

- `docs/agents/HANDOFF.md` - current stage, next allowed step, files changed, commands, verification, and risks for vendor switches.
- `docs/agents/TOOLING.md` - canonical MCP and skills inventory for this project.
- `docs/agents/DECISIONS.md` - architecture and tooling decisions with rationale.
- `docs/agents/AI_WORKFLOW.md` - skills, MCP tools, subagents, prompts, reports, and verification evidence.
- `docs/agents/FEATURE_CHECKLIST.md` - coverage of product requirements and review criteria.
- `README.md` - local launch, environment setup, import, migrations, tests, and technical decisions.

If `docs/agents` does not exist yet, create it during the documentation stage. `AGENTS.md` is the operating manual. Start a new session from that file, then read `HANDOFF.md` only for the live stage.

## Stage Checkpoints

Before each stage:

1. Confirm the goal and expected output.
2. Read current project guidance and relevant files.
3. Identify whether a skill or MCP server applies.
4. Keep changes scoped to the stage.

After each stage:

1. Run the smallest useful verification command.
2. Record the command and result in `docs/agents/AI_WORKFLOW.md` when it affects delivery confidence.
3. Update `docs/agents/FEATURE_CHECKLIST.md` when a requirement is completed.
4. Note architectural decisions in `docs/agents/DECISIONS.md`.

## MCP Usage

- Prefer read-only MCP modes by default.
- Never commit MCP tokens, API keys, cookies, or local absolute secrets.
- Use documentation/fetch MCP for current dependency docs when framework or package behavior matters.
- Use browser automation only for explicit UI smoke checks or when visual behavior must be verified.
- Document which MCP tools were used and why.

## Subagent Usage

Good subagent tasks:

- Explore a large existing codebase for reusable patterns.
- Review a completed implementation area.
- Investigate documentation or dependency behavior.
- Run an isolated checklist against product requirements.

Avoid subagents for tasks that require immediate shared state edits unless the workflow explicitly supports merging their work.

## Completion Gate

Do not mark a stage complete until:

- relevant tests or checks were run,
- failures are either fixed or explicitly documented,
- README/workflow artifacts reflect behavior that changed,
- no secrets were added,
- scope still matches the product design.
