---
name: fordewind-laravel
description: Applies Laravel and PHP engineering standards for the Fordewind car photo voting application. Use when designing, implementing, reviewing, or testing backend, frontend integration, imports, voting, statistics, Docker, or documentation for this project.
---

# Fordewind Laravel

## Project Context

Fordewind is a Laravel web application for pairwise voting on auction car photos and viewing vote statistics by model and production year.

Core product areas:

- Import cars and photos from source JSON data.
- Show two random photos for a selected car model.
- Avoid repeating shown photos within a voting cycle until the available set is exhausted.
- Store votes on the server.
- Show statistics with model/year filters and total vote count.

## Architecture

- Use Laravel 13 conventions and standard framework mechanisms.
- Keep controllers thin: validation, service call, response/resource only.
- Put business logic in focused services:
  - `VotingPairService`
  - `VoteService`
  - `StatisticsService`
  - `CarImportService`
- Use repository contracts only where they clarify boundaries or make tests simpler.
- Keep Eloquent query details in repositories or dedicated query classes, not in controllers.
- Use Form Requests for HTTP validation and Resources for API response shape.
- Read `env()` only in config files. Services receive settings through DI or typed config objects.

## PHP Standards

- Add `declare(strict_types=1);` to new PHP files where possible.
- Declare classes `final` unless framework extension or inheritance is required.
- Prefer explicit arrays, DTOs, and value objects over loosely shaped data.
- Prefer `CarbonImmutable` or `DateTimeImmutable` for date logic.
- Write concise comments only for non-obvious decisions.
- PHPDoc must match signatures when used.

## Frontend Boundaries

- Voting page: jQuery, AJAX, `ezPlus` Tints only.
- Statistics page: Vue 3 only.
- Do not mix jQuery and Vue in one functional area.
- Each page must handle loading, error, empty, disabled, and success states.
- Keep assets under Vite entrypoints scoped to the page that uses them.

## Data And Import

- Imports must be idempotent.
- Preserve raw source payload where useful for display and debugging.
- Copy or expose photos through Laravel storage with stable public paths.
- Never require committed secrets for downloading or importing source data.

## Testing

- Unit-test voting pair selection, cycle reset, vote recording, and statistics aggregation.
- Feature-test AJAX/API routes and validation errors.
- Test import behavior with fixtures and idempotency checks.
- Before claiming work is complete, run the relevant verification command and record the result.
