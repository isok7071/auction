# Feature Checklist

Use this checklist to track product coverage and review readiness.

## Data Import

- [ ] Source JSON files can be imported into MySQL.
- [ ] Source images are copied or exposed through public storage.
- [ ] Import is idempotent.
- [ ] Import supports a local path.
- [ ] Import can optionally fetch or update the source repository.
- [ ] README documents import commands.

## Voting Page

- [ ] Page is implemented with jQuery.
- [ ] User can select a car model.
- [ ] Server returns two different random photos for the selected model.
- [ ] Photos do not repeat in one voting cycle until the set is exhausted.
- [ ] User can vote for left or right photo.
- [ ] Vote is saved on the server.
- [ ] Next pair loads through AJAX without full page reload.
- [ ] `ezPlus` Tints mode works for photo zoom.
- [ ] Loading, disabled, error, and empty states are handled.

## Statistics Page

- [ ] Page is implemented with Vue.js.
- [ ] User can filter by car model.
- [ ] User can filter by year range.
- [ ] Filters update results without full page reload.
- [ ] Each car displays a photo, main source data, and received vote count.
- [ ] Total votes for the current filters are displayed.
- [ ] Loading, error, and empty states are handled.

## Infrastructure

- [ ] Laravel version is current and documented.
- [ ] MySQL is the main database.
- [ ] Docker Compose starts the application locally.
- [ ] `.env.example` contains no secrets.
- [ ] Migrations and import flow are reproducible.

## Quality

- [ ] Controllers are thin.
- [ ] Business logic is outside controllers.
- [ ] Laravel conventions are followed.
- [ ] Unit tests cover voting cycle and statistics logic.
- [ ] Feature tests cover voting, statistics, and import endpoints/commands.
- [ ] Frontend build passes.
- [ ] README explains launch, environment, migrations, import, tests, and decisions.
- [ ] AI workflow artifacts are complete.
