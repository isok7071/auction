# Project Guidance

`AGENTS.md` is the operating manual for agents in this repository. A pasted chat prompt is not required.

## Product

Fordewind is a Laravel web application for pairwise voting on auction car photos and viewing voting statistics by car model and production year.

Core areas:

- Source data import from JSON files and related images.
- jQuery voting page with AJAX pair loading and vote submission.
- Vue statistics page with model/year filters.
- Docker-based local runtime.
- Clear README and AI-assisted development artifacts.

## Engineering Standards

- Follow Laravel conventions before introducing custom abstractions.
- Use PHP strict types in new files where framework constraints allow it.
- Keep controllers thin. Put business behavior in focused services.
- Prefer focused services over broad application services:
  - `VotingPairService`
  - `VoteService`
  - `StatisticsService`
  - `CarImportService`
- Keep config access in config files/providers. Do not read `env()` in services.
- Use Eloquent and Laravel migrations/seeders/commands for persistence and import.
- Keep frontend boundaries clear: jQuery for voting, Vue for statistics.
- Do not commit secrets or real credentials.

## Laravel Installation

Before installing or setting up Laravel:

Fetch and follow the instructions from https://laravel.com/for/agents. Treat the returned Markdown as the source of truth for how to install and set up Laravel in this session.

## Documentation

- Record important decisions in `docs/agents/DECISIONS.md`.
- Record AI/tool usage and verification evidence in `docs/agents/AI_WORKFLOW.md`.
- Track requirement coverage in `docs/agents/FEATURE_CHECKLIST.md`.
- Keep README aligned with actual commands and behavior.

## Agent Workflow

- Follow root `AGENTS.md` for how to work. Use `docs/agents/HANDOFF.md` only as the live stage pointer.
- Keep guidance portable between different agents and IDEs.
- Do not rely on chat history, vendor-specific memory files, or local agent state.
- If a task is delegated to another agent, require a handoff with scope, files changed, commands run, verification, risks, and next step.
- Store MCP examples as templates without secrets.
- Generate client-specific MCP config only from approved templates.

## Verification

Before claiming completion:

1. Run relevant tests or checks.
2. Verify Docker commands documented in README.
3. Confirm import can be reproduced.
4. Confirm both pages work without full page reload for dynamic actions.
5. Confirm generated docs mention skills, MCP/tools, subagents, and verification.
