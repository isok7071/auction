---
name: fordewind-laravel-frontend
overview: Завершить Fordewind двумя независимыми frontend-зонами: jQuery+ezPlus для голосования и Vue 3 для статистики, сохранив session/CSRF JSON-контракты backend.
todos:
  - id: data-model
    content: Создать миграции, Eloquent-модели и фабрики автомобилей, фотографий и голосов.
    status: completed
  - id: import
    content: Реализовать и проверить идемпотентную Artisan-команду локального импорта JSON и JPG.
    status: completed
  - id: voting-backend
    content: Реализовать сессионный выбор пар, безопасную запись голосов и JSON endpoints для jQuery-страницы.
    status: completed
  - id: statistics-backend
    content: Реализовать агрегирование и JSON endpoint статистики для Vue-страницы.
    status: completed
  - id: backend-documentation
    content: Зафиксировать команды, решения, покрытие и handoff backend-этапа.
    status: completed
  - id: frontend-routing-contract
    content: Развести Blade-страницы и JSON routes, добавить полный список моделей для фильтра статистики.
    status: completed
  - id: voting-frontend
    content: Реализовать адаптивную jQuery-страницу голосования с AJAX, CSRF и ezPlus Tints.
    status: completed
  - id: statistics-frontend
    content: Реализовать адаптивную Vue 3-страницу статистики с фильтрами и состояниями интерфейса.
    status: completed
  - id: frontend-verification
    content: Собрать Vite, проверить оба пользовательских сценария в Docker и обновить handoff-артефакты.
    status: completed
isProject: false
---

# Fordewind Backend Contract And Frontend Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: use `executing-plans` to execute this plan task-by-task; use `subagent-driven-development` only when delegation is explicitly enabled. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Finish the Laravel/MySQL application with a responsive jQuery voting page and a separate Vue 3 statistics page on top of the implemented backend.

**Architecture:** Laravel 13 modular monolith with explicit domain boundaries. `app/Domain/{Cars,Voting,Statistics}` contains Eloquent models, repository/storage contracts, and immutable DTOs; `app/Infrastructure` contains Eloquent and public-disk implementations; application services orchestrate the contracts. Controllers only validate and translate HTTP to Resources. The voting cycle lives in the existing database-backed Laravel session under each selected `make + model` value; no new cycle table is necessary.

**Tech Stack:** PHP 8.5, Laravel 13, MySQL 8.4, Blade, Vite 8, Tailwind CSS 4, jQuery, ezPlus, Vue 3, Eloquent, API Resources, PHPUnit 12, Docker Compose.

**Spec:** `Task.md`; `docs/superpowers/specs/2026-09-11-fordewind-laravel-design.md`; current stage in `docs/agents/HANDOFF.md`.

## Global Constraints

- The Docker Compose image/runtime already works; do not reinstall PHP or redesign the Compose stack.
- Use Docker MySQL, never the old local SQLite `.env`, for migrations, import, and tests that need MySQL behavior.
- Keep the agreed `Domain / Infrastructure / Repository` separation: services depend only on domain contracts; Eloquent queries and filesystem operations live in infrastructure implementations; bindings live in `AppServiceProvider`.
- Use immutable typed DTOs at layer boundaries (`ImportedCarDto`, `StoredPhotoDto`, `ImportResultDto`, `UpsertedCarDto`, `VotingPairDto`, `RecordVoteDto`, `StatisticsFiltersDto`, `StatisticsResultDto`). Do not replace simple scalar method arguments or Eloquent relationships with DTOs merely for symmetry.
- All new PHP files use `declare(strict_types=1);` where Laravel allows it and all non-extendable application classes are `final`.
- Controllers stay limited to a Form Request, a service call, and a Resource/JSON response. `env()` is read only from config files.
- Use a local directory import only. The assignment requires a command or seeder, not automatic GitLab download; cloning/updating a remote source is intentionally out of scope.
- Do not trust client-supplied car or photo relationships: a vote must match the pair currently issued to that browser session and model.
- Use web routes that return JSON for voting so Laravel's session and CSRF middleware remain active. Do not create stateless `api.php` routes for the voting cycle.
- Store only normalized fields needed for filtering, display, vote integrity, and import identity; do not add a raw JSON column to the production schema.
- Do not commit `.env`, source-data checkout, images copied into `storage`, credentials, or tokens. Do not create a git commit unless the developer explicitly asks.
- Product behavior follows TDD: add the narrow failing PHPUnit test, observe failure, add the smallest implementation, rerun the same test, then run the task suite.
- After every task, run `vendor/bin/pint --dirty --format agent` for changed PHP and record commands/results in `docs/agents/AI_WORKFLOW.md`.

## Current Implementation State

Tasks 1–5 are implemented in the checkout; their Docker PHPUnit/Pint runtime verification remains recorded as an environment constraint in `HANDOFF.md`. The developer has declared the backend ready for frontend work. Treat the contracts below as the baseline, do not redesign services or migrations, and begin this frontend extension at Task 7. Task 6 remains the backend verification/handoff gate to run when Docker access is available.

## Confirmed Source Contract

The source repository was inspected at commit `294132718c708b337d8c31d098eac93869c8facc`: it contains 1,497 root-level JSON files and 1,497 root-level JPG files; every JSON `Image` value has a matching JPG. Each JSON record contains `AuctionItemId`, `AuctionId`, `Year`, `Make`, `Model`, `Odometer`, `Units`, `Engine`, `Transmission`, `Color`, `Image`, pricing/status fields, and other source fields. Use `AuctionItemId` as the immutable import key and the exact tuple `Make + ' ' + Model` as the selectable model label/key; the snapshot has 73 such values.

## Backend HTTP Contract

All endpoints below are named web routes and return JSON on `Accept: application/json` / AJAX requests:

| Method and URI | Input | Success response |
| --- | --- | --- |
| `GET /voting/models` | none | `{"data":[{"key":"FORD MUSTANG","label":"FORD MUSTANG"}]}` sorted by label; only models with at least two photos |
| `GET /voting/pair?model={key}` | required `model` string | `{"data":{"model":"FORD MUSTANG","left":{photo},"right":{photo}}}` or `{"data":null,"meta":{"reason":"not_enough_photos"}}` |
| `POST /voting/votes` | `model`, `winner_photo_id`, `loser_photo_id` | `201 {"data":{"id":1,"winner_photo_id":2,"loser_photo_id":3}}` |
| `GET /statistics` | optional `model`, `year_from`, `year_to` | `{"data":[{car}],"meta":{"total_votes":12}}` |

`photo` has `id`, `car_id`, and `url`. A statistics `car` has `id`, `make`, `model`, `model_label`, `year`, `odometer`, `units`, `engine`, `transmission`, `color`, `photo`, and `votes_count`. An unknown selected model, invalid year range, duplicate photo IDs, a non-issued pair, or a vote whose photos belong to another model returns Laravel's JSON `422` validation response. A known model with fewer than two remaining photos returns `not_enough_photos`. An issued pair remains valid until the user submits it; retrieving it again before a vote returns that same pair rather than consuming more cards.

## Layer Contracts

| Domain area | Contract and DTOs | Infrastructure implementation |
| --- | --- | --- |
| Cars/import | `CarRepository`, `CarPhotoStorage`; `ImportedCarDto`, `StoredPhotoDto`, `ImportResultDto`, `UpsertedCarDto` | `EloquentCarRepository`, `PublicDiskCarPhotoStorage` |
| Voting | `VotingRepository`, `VoteRepository`; `VotingPairDto`, `RecordVoteDto` | `EloquentVotingRepository`, `EloquentVoteRepository` |
| Statistics | `StatisticsRepository`; `StatisticsFiltersDto`, `StatisticsResultDto` | `EloquentStatisticsRepository` |

`AppServiceProvider` binds every interface to its infrastructure implementation. DTOs are readonly value carriers, never Eloquent replacements: Resources continue to receive Eloquent models/collections returned by repositories through services.

---

### Task 1: Establish the persisted domain model

**Files:**

- Create: `database/migrations/2026_09_12_000001_create_cars_table.php`
- Create: `database/migrations/2026_09_12_000002_create_car_photos_table.php`
- Create: `database/migrations/2026_09_12_000003_create_votes_table.php`
- Create: `app/Domain/Cars/Models/Car.php`
- Create: `app/Domain/Cars/Models/CarPhoto.php`
- Create: `app/Domain/Voting/Models/Vote.php`
- Create: `database/factories/CarFactory.php`
- Create: `database/factories/CarPhotoFactory.php`
- Create: `database/factories/VoteFactory.php`
- Test: `tests/Feature/Database/CarDomainSchemaTest.php`

**Interfaces:**

- Produces: `App\Domain\Cars\Models\Car`, `CarPhoto`, and `App\Domain\Voting\Models\Vote`; `Car::photos()`, `Car::wonVotes()`, `CarPhoto::car()`, `Vote::winnerPhoto()`, `Vote::loserPhoto()`, `Vote::winnerCar()`, and `Vote::loserCar()` relationships.
- Consumes: the existing database-backed `sessions` table from `0001_01_01_000000_create_users_table.php`; do not create a second sessions migration.

- [ ] **Step 1: Write the failing schema and relationship tests**

Create a car with one photo and a vote, then assert the database rows, `Car::photos`, and `Car::wonVotes` work. Assert that two cars cannot share `source_auction_item_id` and two photos cannot share `source_filename`.

```php
$car = Car::factory()->create(['source_auction_item_id' => '342154']);
$winnerPhoto = CarPhoto::factory()->for($car)->create(['source_filename' => '71b32420fd9ba92739b3e06704f7b213.jpg']);
$loserPhoto = CarPhoto::factory()->for($car)->create(['source_filename' => '48d86a78c394dc68cea18117ec223ba4.jpg']);
$vote = Vote::factory()->for($winnerPhoto, 'winnerPhoto')->for($loserPhoto, 'loserPhoto')->create([
    'winner_car_id' => $car->id,
    'loser_car_id' => $car->id,
]);

$this->assertDatabaseHas('cars', ['source_auction_item_id' => '342154']);
$this->assertTrue($car->photos->contains($winnerPhoto));
$this->assertTrue($car->wonVotes->contains($vote));
```

- [ ] **Step 2: Run the focused test and confirm it fails before the domain exists**

Run: `docker compose run --rm app php artisan test --compact tests/Feature/Database/CarDomainSchemaTest.php`

Expected: failure because `Car`, `CarPhoto`, and `Vote` and their tables do not exist.

- [ ] **Step 3: Generate the Laravel files without applying migrations**

Run:

```bash
docker compose run --rm app php artisan make:model Car -mf --no-interaction
docker compose run --rm app php artisan make:model CarPhoto -mf --no-interaction
docker compose run --rm app php artisan make:model Vote -mf --no-interaction
```

Move the generated models into their listed Domain namespaces, update factory imports/namespaces, and keep only the three dedicated migration files above. Do not alter Laravel's `users` migration that already owns `sessions`.

- [ ] **Step 4: Define the exact schema and model casts**

Create these tables:

```text
cars: id; source_auction_item_id string unique; auction_id nullable string;
      make string; model string; year unsignedSmallInteger; odometer nullable unsignedInteger;
      units nullable string; engine nullable string; transmission nullable string; color nullable string;
      timestamps; index(make, model, year)
car_photos: id; car_id foreignId constrained cascadeOnDelete; source_filename string unique;
            storage_path string; checksum char(64); timestamps; index(car_id)
votes: id; session_id string indexed; winner_photo_id and loser_photo_id foreignIds constrained;
       winner_car_id and loser_car_id foreignIds constrained; timestamps
```

In `Car`, cast `year` and `odometer` to integer. In `CarPhoto`, expose `url(): string` using `Storage::disk('public')->url($this->storage_path)`. Factories must create valid linked records by default; `VoteFactory` must create distinct winner/loser photos and assign the matching cars.

- [ ] **Step 5: Apply migrations in Docker and make the schema test pass**

Run:

```bash
docker compose exec app php artisan migrate --force
docker compose run --rm app php artisan test --compact tests/Feature/Database/CarDomainSchemaTest.php
vendor/bin/pint --dirty --format agent
```

Expected: migrations complete against MySQL and the focused test passes.

### Task 2: Implement the idempotent local-source importer

**Files:**

- Create: `config/fordewind.php`
- Create: `app/Services/CarImportService.php`
- Create: `app/Console/Commands/ImportCarsCommand.php`
- Create: `app/Domain/Cars/Contracts/CarRepository.php`
- Create: `app/Domain/Cars/Contracts/CarPhotoStorage.php`
- Create: `app/Domain/Cars/Data/ImportedCarDto.php`
- Create: `app/Domain/Cars/Data/StoredPhotoDto.php`
- Create: `app/Domain/Cars/Data/ImportResultDto.php`
- Create: `app/Domain/Cars/Data/UpsertedCarDto.php`
- Create: `app/Infrastructure/Persistence/Eloquent/EloquentCarRepository.php`
- Create: `app/Infrastructure/Storage/PublicDiskCarPhotoStorage.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Modify: `routes/console.php`
- Test: `tests/Feature/Console/ImportCarsCommandTest.php`
- Test fixture: `tests/Fixtures/import-source/auction-item-342154.json`
- Test fixture: `tests/Fixtures/import-source/71b32420fd9ba92739b3e06704f7b213.jpg`

**Interfaces:**

- Produces: `CarImportService::import(string $sourcePath): ImportResultDto` and `cars:import {source : Absolute or container-visible local source directory}`.
- Consumes: root-level `*.json` records and image names from each record's required `Image` key; `Storage::disk('public')` stores images at `cars/{Image}`.
- `CarRepository::upsert(ImportedCarDto $data, StoredPhotoDto $photo): UpsertedCarDto` is the only import write boundary. `CarPhotoStorage::store(string $sourcePath, string $sourceFilename): StoredPhotoDto` is the only filesystem boundary. `ImportResultDto` has `public int $createdCars`, `public int $updatedCars`, and `public int $importedPhotos`.

- [ ] **Step 1: Add fixture-based failing command tests**

The test must run `cars:import` against `tests/Fixtures/import-source`, fake the `public` disk, and prove: the car is created from the known JSON fields; `cars/71b32420fd9ba92739b3e06704f7b213.jpg` exists; its stored checksum equals `hash_file('sha256', $fixtureImage)`; a second invocation leaves one car/one photo and reports an update; a missing `Image` file fails without inserting that car.

```php
Storage::fake('public');

$this->artisan('cars:import', ['source' => base_path('tests/Fixtures/import-source')])
    ->expectsOutputToContain('created=1')
    ->assertExitCode(0);

$this->assertDatabaseHas('cars', [
    'source_auction_item_id' => '342154',
    'make' => 'TOYOTA',
    'model' => 'TUNDRA LIMITED',
    'year' => 2003,
]);
Storage::disk('public')->assertExists('cars/71b32420fd9ba92739b3e06704f7b213.jpg');
```

- [ ] **Step 2: Run the command test and confirm the command is absent**

Run: `docker compose run --rm app php artisan test --compact tests/Feature/Console/ImportCarsCommandTest.php`

Expected: failure because `cars:import` is not registered.

- [ ] **Step 3: Implement source validation, mapping, upsert, and stable file copy**

`config/fordewind.php` contains only a default source directory using `storage_path('app/import-source')`; the required command argument overrides it only after `ImportCarsCommand` resolves it. The service must:

1. Reject a missing/non-directory source, invalid JSON, a record missing `AuctionItemId`, `Make`, `Model`, `Year`, or `Image`, and an image name whose `basename()` differs from the value.
2. Read each root-level JSON file using `JSON_THROW_ON_ERROR`; check the matching root-level image exists before mutating that record.
3. Map each decoded row to `ImportedCarDto`, including `AuctionId`, `Make`, `Model`, `Year`, `Odometer`, `Units`, `Engine`, `Transmission`, and `Color`; pass it to `CarRepository::upsert()`.
4. Let `PublicDiskCarPhotoStorage` hash the image with SHA-256 and write it with `Storage::disk('public')->putFileAs('cars', new File($sourcePath), $sourceFilename)`. It returns `StoredPhotoDto(storagePath: "cars/{$sourceFilename}", checksum: $checksum)`. `EloquentCarRepository` alone performs the photo `updateOrCreate` keyed by `source_filename`.
5. Return exact created/updated/photo counters and render them from the command. Do not delete cars or photos absent from a later import.

`EloquentCarRepository` uses `DB::transaction()` for car/photo upserts. The public-disk write is idempotent by filename and checksum; document that an interrupted process can leave an unreferenced file but cannot create duplicate database rows. Bind `CarRepository` to `EloquentCarRepository` and `CarPhotoStorage` to `PublicDiskCarPhotoStorage` in `AppServiceProvider`; inject contracts into `CarImportService`.

- [ ] **Step 4: Make import URLs available to the web server**

Run: `docker compose exec app php artisan storage:link`

Add the command's intended container usage to the plan's documentation task: clone/copy the external source under `storage/app/import-source`, then run `docker compose exec app php artisan cars:import storage/app/import-source`. Do not add the source checkout to git.

- [ ] **Step 5: Rerun tests and format changed PHP**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Console/ImportCarsCommandTest.php
vendor/bin/pint --dirty --format agent
```

Expected: first import creates one car/photo, the second updates rather than duplicates, and invalid input exits non-zero.

### Task 3: Build the server-side voting-pair cycle

**Files:**

- Create: `app/Services/VotingPairService.php`
- Create: `app/Domain/Voting/Contracts/VotingRepository.php`
- Create: `app/Domain/Voting/Data/VotingPairDto.php`
- Create: `app/Infrastructure/Persistence/Eloquent/EloquentVotingRepository.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Create: `app/Http/Controllers/VotingModelController.php`
- Create: `app/Http/Controllers/VotingPairController.php`
- Create: `app/Http/Requests/ShowVotingPairRequest.php`
- Create: `app/Http/Resources/VotingModelResource.php`
- Create: `app/Http/Resources/CarPhotoResource.php`
- Create: `app/Http/Resources/VotingPairResource.php`
- Modify: `routes/web.php`
- Test: `tests/Unit/Services/VotingPairServiceTest.php`
- Test: `tests/Feature/Http/VotingPairEndpointTest.php`

**Interfaces:**

- Produces: `VotingPairService::models(): Collection`, `VotingPairService::pairFor(string $modelKey, Store $session): ?VotingPairDto`, `VotingPairService::pendingPair(string $modelKey, Store $session): ?array`, `VotingPairService::clearPendingPair(string $modelKey, Store $session): void`, and routes `voting.models` / `voting.pair`.
- `VotingRepository::modelOptions(): Collection`, `VotingRepository::hasModel(string $modelKey): bool`, `VotingRepository::photoIdsForModel(string $modelKey): Collection`, and `VotingRepository::photosByIds(array $photoIds): Collection` isolate all Eloquent selection. `VotingPairDto` is a readonly DTO with `public string $modelKey`, `public CarPhoto $left`, and `public CarPhoto $right`.
- Consumes: imported photos grouped by exact `CONCAT(make, ' ', model)` and Laravel's existing session store.

- [ ] **Step 1: Write unit tests for availability, no repeat, and reset**

Build five photos for one `FORD MUSTANG` model and use an array session store. After each accepted pair, call `VotingPairService::clearPendingPair()` to emulate a successful vote. Assert that the first two pairs contain four distinct IDs, the third pair contains the fifth previously unseen ID plus exactly one ID from the first four, and no ID is repeated before the fifth ID has been issued. Assert that a model with one photo returns `null`; repeated `pairFor()` before marking the pair voted returns the same two IDs.

```php
$first = $service->pairFor('FORD MUSTANG', $session);
$again = $service->pairFor('FORD MUSTANG', $session);

$this->assertSame([$first->left->id, $first->right->id], [$again->left->id, $again->right->id]);
```

- [ ] **Step 2: Run the unit test and confirm it fails**

Run: `docker compose run --rm app php artisan test --compact tests/Unit/Services/VotingPairServiceTest.php`

Expected: failure because `VotingPairService` is absent.

- [ ] **Step 3: Implement an explicit session deck and pending-pair state**

Inject `VotingRepository` into `VotingPairService` and bind it to `EloquentVotingRepository` in `AppServiceProvider`. Use session keys `voting.decks.{sha1(modelKey)}` and `voting.pending_pairs.{sha1(modelKey)}`. A deck is an array of photo IDs. `pairFor()` first returns the pending IDs only if both photos still exist and match the requested model. Otherwise it asks the repository for model photo IDs ordered by ID and returns `null` if fewer than two exist. Remove the next two IDs from a shuffled deck when at least two remain. When exactly one ID remains, issue that final unseen ID with one ID drawn from a newly shuffled deck that excludes the final ID, then persist the rest of that new deck. This carries the odd photo into the boundary pair instead of silently discarding it, so every photo is issued before any repeat. Persist the reduced deck and `['left' => id, 'right' => id]` pending pair. `clearPendingPair()` forgets only that model's pending key and leaves its deck unchanged; Task 4 calls it after a durable vote write.

`EloquentVotingRepository::modelOptions()` joins `car_photos`, groups by `make`/`model`, and returns `CONCAT(make, ' ', model) AS model_key` only when `COUNT(car_photos.id) >= 2`; this counts photos across all cars of the selected model rather than requiring two photos on one car. It orders by make/model. `hasModel()` checks the exact concatenated key without a photo-count constraint. Do not use SQL random ordering for every pair after the deck is built. Apply Laravel route session blocking to both `voting.pair` and `voting.votes` with `->block(10, 10)`, so concurrent AJAX requests from one browser serialize before they read or change the session deck.

- [ ] **Step 4: Add failing HTTP tests and implement the JSON endpoints**

Feature-test `GET /voting/models` returns only models with two photos; `GET /voting/pair` returns `422` for a missing, malformed, or unknown model; it returns a photo payload with `/storage/cars/...` URLs for a valid model and the explicit `not_enough_photos` meta payload for a known one-photo model. Implement the request rule `model => ['required', 'string', 'max:255']`; the controller asks `hasModel()` after request validation, while the service decides whether a known model has enough photos.

```php
$this->getJson(route('voting.pair', ['model' => 'FORD MUSTANG']))
    ->assertOk()
    ->assertJsonPath('data.model', 'FORD MUSTANG')
    ->assertJsonStructure(['data' => ['left' => ['id', 'car_id', 'url'], 'right' => ['id', 'car_id', 'url']]]);
```

- [ ] **Step 5: Verify the voting-pair task**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Unit/Services/VotingPairServiceTest.php tests/Feature/Http/VotingPairEndpointTest.php
vendor/bin/pint --dirty --format agent
```

Expected: selection state persists per session/model, no photo is repeated within a usable deck, and the HTTP contract matches the table above.

### Task 4: Record only valid, issued votes

**Files:**

- Create: `app/Services/VoteService.php`
- Create: `app/Domain/Voting/Contracts/VoteRepository.php`
- Create: `app/Domain/Voting/Data/RecordVoteDto.php`
- Create: `app/Infrastructure/Persistence/Eloquent/EloquentVoteRepository.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Create: `app/Http/Controllers/VoteController.php`
- Create: `app/Http/Requests/StoreVoteRequest.php`
- Create: `app/Http/Resources/VoteResource.php`
- Modify: `app/Services/VotingPairService.php`
- Modify: `routes/web.php`
- Test: `tests/Unit/Services/VoteServiceTest.php`
- Test: `tests/Feature/Http/VoteEndpointTest.php`

**Interfaces:**

- Produces: `VoteService::record(string $modelKey, int $winnerPhotoId, int $loserPhotoId, Store $session): Vote` and route `voting.votes`.
- `VoteRepository::findPhotosWithCars(array $photoIds): Collection` and `VoteRepository::create(RecordVoteDto $data): Vote` are the vote persistence boundary.
- Consumes: the pending pair written by `VotingPairService`; on success it removes `voting.pending_pairs.{sha1(modelKey)}` while leaving the remaining deck intact.

- [ ] **Step 1: Write failing unit tests for vote integrity**

Create and issue a pair, record a vote, then assert one `votes` row contains the browser session ID plus correct winner/loser photo and car IDs and the pending pair is cleared. Assert each invalid case throws a validation exception without a row: identical IDs, inverted/nonmatching pair, photo from a different model, and no pending pair.

```php
$vote = $service->record('FORD MUSTANG', $pair->left->id, $pair->right->id, $session);

$this->assertSame($pair->left->car_id, $vote->winner_car_id);
$this->assertFalse($session->has('voting.pending_pairs.'.sha1('FORD MUSTANG')));
```

- [ ] **Step 2: Run the unit test and confirm it fails**

Run: `docker compose run --rm app php artisan test --compact tests/Unit/Services/VoteServiceTest.php`

Expected: failure because `VoteService` does not exist.

- [ ] **Step 3: Implement atomic validation, persistence, and pending-pair clearing**

`StoreVoteRequest` validates `model` as a required string up to 255 and both IDs as required distinct integer values. `VoteService` reloads both `CarPhoto` rows with their cars through `VoteRepository`, proves the unordered submitted IDs equal the pending pair, and proves both photos' `make + ' ' + model` equal the request model. It creates `RecordVoteDto(sessionId, winnerPhotoId, loserPhotoId, winnerCarId, loserCarId)` and passes it to `VoteRepository::create()` inside the repository transaction; only after that succeeds it calls `VotingPairService::clearPendingPair()`. Bind `VoteRepository` in `AppServiceProvider`. Use a `ValidationException::withMessages()` keyed to the offending input rather than returning a generic exception.

- [ ] **Step 4: Add HTTP tests and route/controller/resource**

Feature-test a full same-client sequence (`GET /voting/pair`, then `POST /voting/votes`) succeeds with 201 and returns exactly `id`, `winner_photo_id`, and `loser_photo_id`. Test tampered IDs and a second POST of the already consumed pair return 422 and preserve the vote count. Add the named POST route in the web middleware group with `->block(10, 10)`; do not exempt it from CSRF.

- [ ] **Step 5: Verify the vote task**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Unit/Services/VoteServiceTest.php tests/Feature/Http/VoteEndpointTest.php
vendor/bin/pint --dirty --format agent
```

Expected: one valid click makes one durable vote; forged, duplicate, stale, or cross-model submissions do not.

### Task 5: Aggregate filtered statistics for the Vue client

**Files:**

- Create: `app/Services/StatisticsService.php`
- Create: `app/Domain/Statistics/Contracts/StatisticsRepository.php`
- Create: `app/Domain/Statistics/Data/StatisticsFiltersDto.php`
- Create: `app/Domain/Statistics/Data/StatisticsResultDto.php`
- Create: `app/Infrastructure/Persistence/Eloquent/EloquentStatisticsRepository.php`
- Modify: `app/Providers/AppServiceProvider.php`
- Create: `app/Http/Controllers/StatisticsController.php`
- Create: `app/Http/Requests/IndexStatisticsRequest.php`
- Create: `app/Http/Resources/StatisticsCarResource.php`
- Create: `app/Http/Resources/StatisticsCollection.php`
- Modify: `routes/web.php`
- Test: `tests/Unit/Services/StatisticsServiceTest.php`
- Test: `tests/Feature/Http/StatisticsEndpointTest.php`

**Interfaces:**

- Produces: `StatisticsService::forFilters(?string $modelKey, ?int $yearFrom, ?int $yearTo): StatisticsResultDto` and named route `statistics.index`.
- `StatisticsFiltersDto` and `StatisticsResultDto` are readonly DTOs in `app/Domain/Statistics/Data`. `StatisticsRepository::find(StatisticsFiltersDto $filters): Collection` owns the aggregate query.
- Consumes: `votes.winner_car_id` as the definition of a car's received vote. A lost comparison does not increment the displayed count.

- [ ] **Step 1: Write failing aggregation tests**

Create cars across two model keys and three years; create won votes of 3, 2, and 0. Assert model filtering uses the exact make/model key, inclusive years filter from/to, results include zero-vote eligible cars, rows order by `votes_count DESC`, then make/model/year/id, and `totalVotes` is the sum of rows after filtering rather than total table votes.

```php
$result = $service->forFilters('FORD MUSTANG', 2005, 2010);

$this->assertSame(3, $result->totalVotes);
$this->assertSame([$matchingCar->id, $zeroVoteCar->id], $result->cars->pluck('id')->all());
```

- [ ] **Step 2: Run the unit test and confirm it fails**

Run: `docker compose run --rm app php artisan test --compact tests/Unit/Services/StatisticsServiceTest.php`

Expected: failure because `StatisticsService` is absent.

- [ ] **Step 3: Implement one eager-loaded aggregate query**

`StatisticsService` maps scalar request values to `StatisticsFiltersDto` and passes it to the injected repository. `EloquentStatisticsRepository` starts from `Car::query()->with(['photos' => fn ($query) => $query->orderBy(CarPhoto::FIELD_ID)->limit(1)])` and `withCount('wonVotes as votes_count')`. When `modelKey` is not null, apply a parameterized model query using the Eloquent model field constants; apply year bounds only when supplied. Return the complete, ordered collection; the service creates `StatisticsResultDto` and calculates `totalVotes` from its `votes_count` collection, avoiding one query per row. Bind `StatisticsRepository` in `AppServiceProvider`.

- [ ] **Step 4: Define HTTP validation, Resources, and endpoint tests**

`IndexStatisticsRequest` rules are `model => ['nullable', 'string', 'max:255']`, `year_from => ['nullable', 'integer', 'between:1886,'.now()->year]`, `year_to => ['nullable', 'integer', 'between:1886,'.now()->year, 'gte:year_from']`. Feature-test unfiltered data, combined model/year filtering, `year_to < year_from` as JSON 422, a result with `data: []` and `meta.total_votes: 0`, and resource fields/first-photo URL. `StatisticsCollection` returns the resource collection plus `meta.total_votes`; do not place aggregate total on every car row.

- [ ] **Step 5: Verify the statistics task**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Unit/Services/StatisticsServiceTest.php tests/Feature/Http/StatisticsEndpointTest.php
vendor/bin/pint --dirty --format agent
```

Expected: filter semantics, zero-vote rows, vote totals, response shape, and validation are proven by tests.

### Task 6: Integrate backend verification and hand off the frontend contracts

**Files:**

- Modify: `README.md`
- Modify: `docs/agents/HANDOFF.md`
- Modify: `docs/agents/DECISIONS.md`
- Modify: `docs/agents/AI_WORKFLOW.md`
- Modify: `docs/agents/FEATURE_CHECKLIST.md`
- Modify: `.agents/plans/fordewind-laravel-design.md`

**Interfaces:**

- Produces: a reproducible backend handoff and the endpoint/JSON contract the frontend tasks consume.
- Consumes: all backend routes, the import command, PHPUnit evidence, and Docker runtime.

- [ ] **Step 1: Run the complete backend suite in Docker**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Database tests/Feature/Console tests/Unit/Services tests/Feature/Http
vendor/bin/pint --dirty --format agent
docker compose exec app php artisan route:list --path=voting --path=statistics
```

Expected: all backend tests pass, Pint has no remaining edits, and the route list exposes the four contract endpoints with web middleware.

- [ ] **Step 2: Run the import/runtime smoke test without committing source data**

Copy or clone the external source into ignored `storage/app/import-source`, then run:

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan cars:import storage/app/import-source
docker compose exec app php artisan cars:import storage/app/import-source
```

Expected: the first invocation reports creates, the second reports updates/no duplicates, and images are reachable under `/storage/cars/{filename}`. Record only counts and commands, not local paths or credentials.

- [ ] **Step 3: Update Russian developer documentation**

In `README.md`, add the exact import prerequisite, `storage:link`, `cars:import` command, idempotency guarantee, and the four JSON endpoint summaries. Keep the page UI implementation explicitly out of this backend stage. Do not claim the frontend is complete.

- [ ] **Step 4: Update the vendor-neutral agent artifacts**

In `DECISIONS.md`, record local-only import, `AuctionItemId` uniqueness, `Make + Model` model key, public-disk image path, web-session routes, and winner-only statistics. In `AI_WORKFLOW.md`, log this planning/implementation skill use, source inspection result, tests, migration/import smoke evidence, and failures if any. Check only completed backend requirements in `FEATURE_CHECKLIST.md`. Move `HANDOFF.md` to the next allowed frontend stage and link this plan plus the exact backend contract.

- [ ] **Step 5: Perform the delivery hygiene check**

Run:

```bash
git status --short
rg -n --hidden --glob '!.git/**' --glob '!vendor/**' --glob '!node_modules/**' --glob '!.agents/**' --glob '!.claude/**' --glob '!.env.example' '(BEGIN [A-Z ]*PRIVATE KEY|DB_PASSWORD=.+[^f]|AWS_SECRET_ACCESS_KEY=.+[^$])' .
```

Expected: no source checkout, copied images, `.env`, real credentials, or private keys are staged/tracked; investigate any scanner match before handoff.

## Plan Self-Review

- **Spec coverage:** Tasks 1–2 cover MySQL/Eloquent data model and reproducible JSON/JPG import; Tasks 3–4 cover model selection, random distinct pairs, no-repeat cycle, server persistence, and AJAX-ready errors; Task 5 covers model/year statistics and total votes. Tasks 7–10 below cover the missing jQuery/AJAX/ezPlus voting UI, Vue statistics UI, responsive layout, browser states, build, smoke, and delivery artifacts.
- **Decomposition:** Each task ends in an independently runnable test or build/smoke gate. Import precedes voting; voting precedes statistics because `votes` defines the aggregate; Task 7 establishes the page and selector contracts before either isolated frontend entrypoint consumes them.
- **Consistency:** `modelKey` always means exact `Make + ' ' + Model`; photos are served from `cars/{Image}`; received votes always mean `votes.winner_car_id`; session keys use `sha1(modelKey)`; services use Domain contracts only; Eloquent/database/filesystem work remains in Infrastructure implementations.
- **Placeholder scan:** No task delegates a UI state, route, request field, component responsibility, or verification command to an unspecified later decision. The only runtime prerequisite is Docker access already documented in `HANDOFF.md`.

---

### Task 7: Separate page routes from JSON endpoints and expose statistics filter options

**Why this task exists:** `GET /statistics` is currently the JSON endpoint, so it cannot also be the human statistics page. Also, `GET /voting/models` deliberately excludes models with fewer than two photos and is therefore not a valid source for the statistics filter. Make these two small contract corrections before writing either UI.

**Files:**

- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/voting/index.blade.php`
- Create: `resources/views/statistics/index.blade.php`
- Create: `resources/js/voting.js`
- Create: `resources/js/statistics.js`
- Create: `app/Http/Resources/StatisticsModelResource.php`
- Modify: `vite.config.js`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/StatisticsController.php`
- Modify: `app/Services/StatisticsService.php`
- Modify: `app/Domain/Statistics/Contracts/StatisticsRepository.php`
- Modify: `app/Infrastructure/Persistence/Eloquent/EloquentStatisticsRepository.php`
- Modify: `tests/Feature/Http/StatisticsEndpointTest.php`
- Create: `tests/Feature/Http/FrontendPageEndpointTest.php`

**Interfaces:**

- Produces named Blade pages `GET /` (`voting.page`) and `GET /statistics` (`statistics.page`), JSON `GET /statistics/data` (`statistics.data`), and JSON `GET /statistics/models` (`statistics.models`).
- Preserves the exact current statistics response body under `/statistics/data`: `data` rows plus `meta.total_votes`; all frontend data requests send `Accept: application/json`.
- Produces `StatisticsService::models(): Collection`, `StatisticsRepository::modelOptions(): Collection`, and `StatisticsModelResource` rows shaped as `{key: string, label: string}`. The selector receives every distinct model key; models with one photo remain eligible for statistics.
- Consumes the existing `Car::selectModelKey()` scope and `StatisticsController` request/resource style. Do not reuse `VotingPairService::models()` because that endpoint intentionally applies a two-photo eligibility rule.

- [x] **Step 1: Add failing feature tests for the route split and full statistics selector**

Add a test that creates a car with a single photo and asserts it is absent from `/voting/models` but present in `/statistics/models`. Add tests that `/` renders the `voting-page` mount element, `/statistics` renders the `statistics-page` mount element, and `/statistics/data` retains the existing filtered JSON and validation behavior.

```php
$singlePhotoCar = Car::factory()->create([
    Car::FIELD_MAKE => 'SAAB',
    Car::FIELD_MODEL => '900',
]);
CarPhoto::factory()->for($singlePhotoCar)->create();

$this->getJson('/voting/models')->assertJsonMissing(['key' => 'SAAB 900']);
$this->getJson('/statistics/models')
    ->assertOk()
    ->assertJsonFragment(['key' => 'SAAB 900', 'label' => 'SAAB 900']);

$this->get('/')->assertOk()->assertSee('id="voting-page"', false);
$this->get('/statistics')->assertOk()->assertSee('id="statistics-page"', false);
```

- [x] **Step 2: Run the new tests and confirm the expected red state**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php tests/Feature/Http/StatisticsEndpointTest.php
```

Expected: the test fails because `/statistics` still returns JSON and the page/selector routes and views do not exist.

- [x] **Step 3: Implement the minimal page and selector contract**

Create a single semantic Blade layout containing the document title slot, responsive header navigation (`/` and `/statistics`), `@vite` assets, and `<meta name="csrf-token" content="{{ csrf_token() }}">`. Make the root page a `Route::view('/', 'voting.index')`; make `/statistics` a `Route::view('/statistics', 'statistics.index')`. Move the existing `StatisticsController::index()` JSON route unchanged to `/statistics/data` and name it `statistics.data`.

Add `StatisticsController::models(StatisticsService $service): AnonymousResourceCollection`. `StatisticsService::models()` delegates to `StatisticsRepository::modelOptions()`. Its Eloquent implementation starts at `Car::query()`, selects the model-key expression, groups by `make` and `model`, orders by both, and returns the collection. `StatisticsModelResource` returns the model key as both `key` and `label`. Do not query cars from Blade and do not add a new frontend-only table or cache.

- [x] **Step 4: Implement deliberately minimal page shells and buildable entrypoint placeholders**

`voting.index` contains only an empty `#voting-page` ownership root and `noscript` guidance; Task 8 adds its jQuery-controlled elements after its red shell test. `statistics.index` contains only `#statistics-page`, Vue's `v-cloak` root and a `noscript` message; Task 9 adds its data-route attribute and Vue-rendered content. Extend Vite's Laravel input list with both page modules and create buildable placeholder modules before rendering either view. Both views extend the layout and load their own entrypoint with the common CSS:

```blade
@vite(['resources/css/app.css', 'resources/js/voting.js'])
{{-- statistics index uses resources/js/statistics.js instead --}}
```

Do not place Vue directives in the voting page or bind jQuery to any element in the statistics page.

- [x] **Step 5: Verify the corrected contract and format PHP**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php tests/Feature/Http/StatisticsEndpointTest.php tests/Feature/Http/VotingPairEndpointTest.php
docker compose run --rm app vendor/bin/pint --dirty --format agent
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
```

Expected: page routes return HTML, statistics data retains its JSON contract at `/statistics/data`, statistics models include one-photo cars, and voting models retain their two-photo rule.

### Task 8: Build the jQuery voting page and ezPlus Tints interaction

**Files:**

- Modify: `package.json`
- Modify: `package-lock.json`
- Modify: `resources/css/app.css`
- Modify: `resources/views/voting/index.blade.php`
- Create: `resources/js/voting.js`

**Interfaces:**

- Produces a jQuery-only voting UI using `GET /voting/models`, `GET /voting/pair?model={modelKey}`, and `POST /voting/votes` with `{model, winner_photo_id, loser_photo_id}`.
- The module sends `Accept: application/json` on every request and `X-CSRF-TOKEN` from the layout meta tag on the POST. It keeps the current issued pair in module state and never chooses photo IDs or a next pair itself.
- States are exactly `initial`, `loading_models`, `loading_pair`, `ready`, `submitting`, `empty`, and `error`. Both vote buttons are disabled unless state is `ready`.

- [x] **Step 1: Install only the required frontend libraries and add the Vite inputs**

Add runtime dependencies `jquery`, `ez-plus`, and `vue`; do not add a test runner, UI kit, HTTP client, or component library. The separate Vite inputs were created in Task 7; keep the common `resources/css/app.css` entry. Confirm package-lock is updated by npm rather than edited manually.

```bash
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm install jquery ez-plus vue
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
```

Expected: Vite can resolve all three entry dependencies. If `ez-plus` needs jQuery on `window`, set `window.$` and `window.jQuery` immediately before importing the plugin; do not add a second jQuery copy through a CDN.

- [x] **Step 2: Add the red page-shell tests before behaviour code**

Extend `FrontendPageEndpointTest` to assert the voting HTML has the model control, distinct left/right action controls, CSRF meta tag, and only the voting entrypoint. The test proves the stable DOM contract which `resources/js/voting.js` consumes; no JavaScript test framework is installed, so interactive decisions are verified through the browser smoke in Task 10 rather than adding an unapproved dependency.

```php
$this->get('/')
    ->assertOk()
    ->assertSee('id="voting-model"', false)
    ->assertSee('data-side="left"', false)
    ->assertSee('data-side="right"', false)
    ->assertSee('csrf-token', false);
```

- [x] **Step 3: Run the shell test and confirm the expected red state**

Run: `docker compose run --rm app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php`

Expected: failure until the semantic controls and Vite entrypoint are present.

- [x] **Step 4: Implement the smallest stateful jQuery module and its owned markup**

Add the jQuery-owned form, `#voting-model`, `aria-live` status, left/right photo panels, and `data-side="left"` / `data-side="right"` buttons inside `#voting-page`; the images begin without `src` and both buttons begin disabled. On DOM ready, cache those owned elements, configure `$.ajaxSetup()` with JSON acceptance and the CSRF header, then load model options. Render an enabled placeholder first; if `data` is empty, show the empty state and leave selection disabled. On a selection change, clear any old pair, disable both actions, request `/voting/pair`, and render either the two returned photos or the `not_enough_photos` message.

For a left/right button, derive `winner_photo_id` from the matching current pair side and `loser_photo_id` from the other side. While the POST is in flight, disable the select and both buttons. A `201` response immediately loads the next pair for the same model. A failed POST keeps the current images and re-enables the choice so a transient failure cannot silently discard the server-issued pair. For `422`, display the first field error; for other failures, display `responseJSON.message` or a Russian network fallback. Guard every callback with the selected model/current request token so an older response cannot overwrite a newer selection.

- [x] **Step 5: Attach and refresh ezPlus only for displayed voting images**

After each successful image render, remove the old plugin wrapper/window if present, set each image `src` and `alt` using the selected model plus side, then initialize `$(image).ezPlus()` with `tint: true`, a visible tint colour/opacity, and a bounded zoom window. Initialize after the image `load` event so natural dimensions are available. The image remains a normal responsive `<img>` when the plugin cannot initialize; zoom failure must not block voting.

- [x] **Step 6: Apply the responsive Tailwind layout**

Use Tailwind 4 utilities already enabled in `resources/css/app.css`; do not create `tailwind.config.js` or use v3 directives. The voting controls and status occupy a centred readable column. At `md` and above, render the two equal photo panels in a grid; below `md`, stack them. Use `gap-*` for sibling spacing, visible focus rings, explicit disabled styles, `aria-live="polite"` status, and buttons with non-colour labels such as `Левая нравится больше`. Keep plugin-specific CSS narrowly scoped to `.voting-photo` and its ezPlus generated elements.

- [x] **Step 7: Run the focused test and production build**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php tests/Feature/Http/VotingPairEndpointTest.php tests/Feature/Http/VoteEndpointTest.php
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
```

Expected: the Blade contract and all voting JSON contracts pass, and the built voting bundle contains jQuery/ezPlus without a module-resolution error.

### Task 9: Build the Vue 3 statistics page

**Files:**

- Modify: `resources/css/app.css`
- Modify: `resources/views/statistics/index.blade.php`
- Create: `resources/js/statistics.js`
- Modify: `tests/Feature/Http/FrontendPageEndpointTest.php`

**Interfaces:**

- Produces a Vue 3-only app mounted once at `#statistics-page`; it calls `GET /statistics/models` for the complete native selector and `GET /statistics/data` with optional `model`, `year_from`, `year_to`, `page`, and `per_page` query parameters.
- Reactive state is `{ models, filters, cars, totalVotes, pagination, isLoadingModels, isLoadingResults, error, hasLoaded }`. The displayed page, pagination metadata, and `totalVotes` are assigned only from the same successful response.
- Filters are submitted with an explicit `Применить` button and reset with `Сбросить`; neither filtering nor pagination performs a browser navigation. The model selector includes models without a votable pair, while result rows are limited to 24 per page by default.

- [x] **Step 1: Add the failing statistics-page shell test**

Extend `FrontendPageEndpointTest` to require the statistics mount root, no voting controls, and its `data-statistics-api` attribute. This verifies the server-to-Vue contract before Vue behaviour code exists.

```php
$this->get('/statistics')
    ->assertOk()
    ->assertSee('id="statistics-page"', false)
    ->assertSee('data-statistics-api="/statistics/data"', false)
    ->assertDontSee('id="voting-model"', false);
```

- [x] **Step 2: Run the shell test and confirm the expected red state**

Run: `docker compose run --rm app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php`

Expected: failure until the page exposes the Vue mount's statistics data route.

- [x] **Step 3: Implement fetch, validation, and race-safe Vue state**

Read the API base path from `#statistics-page.dataset.statisticsApi`. Use `createApp` from `vue`, native `fetch`, and `URLSearchParams`; do not import jQuery or axios. On mount, request the model options and then load unfiltered statistics. When applying filters, trim `model`, omit blank values, and validate in the client that supplied year values are integers and `year_from <= year_to`; present a Russian validation message without making an invalid request. Use an `AbortController` plus monotonically increasing request sequence for result requests: abort a prior request, and only the latest non-aborted response may update `cars`, `totalVotes`, `error`, and `hasLoaded`.

On a non-2xx response, parse Laravel's JSON `errors` first, then its `message`, then a network fallback. Preserve the most recent successful table/cards while an updated request is loading; replace them only on success. `Сбросить` clears the three filters and reloads the unfiltered endpoint. The total uses only `payload.meta.total_votes`, never a client-side recomputation.

- [x] **Step 4: Render all required UI states and car data**

Render: initial/model-options loading; result loading; a dismissible error block; an empty state only after a successful zero-row response; and success content. The success view contains an accessible filter form, selected-filter summary, `Всего голосов: {{ totalVotes }}`, and every required car field: first photo (or a `Фото отсутствует` fallback), `model_label`, year, odometer with units when both are present, engine, transmission, colour, and `votes_count`.

Use a semantic table for `md` and wider viewports, and a separate labelled card list below `md`; both render from the same `cars` array keyed by car ID. Vue interpolations must render source fields rather than `v-html`. Inputs have labels, `min="1886"`, `:max="new Date().getFullYear()"`, and disabled controls while their corresponding request is in flight.

- [x] **Step 5: Apply responsive Tailwind 4 styles without crossing page ownership**

Keep statistics classes under the Vue-rendered template and common tokens in `app.css`. Use a max-width container, `gap-*` layout, responsive filter grid, a horizontally scrollable table wrapper for medium screens, and cards for mobile. Include focus-visible styles and `v-cloak` CSS so Vue markup is not flashed before mount. Do not add jQuery selectors, ezPlus markup, or inline script tags to this page.

- [x] **Step 6: Run the focused test and production build**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Http/FrontendPageEndpointTest.php tests/Feature/Http/StatisticsEndpointTest.php
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
```

Expected: the page shell and statistics JSON contract pass, and Vue compiles as a separate Vite bundle.

### Task 10: Verify both user flows and hand off the completed frontend stage

**Files:**

- Modify: `README.md`
- Modify: `docs/agents/HANDOFF.md`
- Modify: `docs/agents/DECISIONS.md`
- Modify: `docs/agents/AI_WORKFLOW.md`
- Modify: `docs/agents/FEATURE_CHECKLIST.md`
- Modify: `.agents/plans/fordewind-laravel-design.md`

**Interfaces:**

- Produces reproducible instructions for the two page URLs, the route split, Vite build/dev mode, and frontend verification evidence.
- Consumes the imported local source, Docker app/nginx/mysql/node services, and completed Tasks 7–9. It does not add seed data, a browser-test package, or a commit.

- [x] **Step 1: Run the complete focused automated suite and formatting/build gates**

Run:

```bash
docker compose run --rm app php artisan test --compact tests/Feature/Console tests/Feature/Database tests/Unit/Services tests/Feature/Http
docker compose run --rm app vendor/bin/pint --dirty --format agent
docker compose --profile assets run --rm --user "$(id -u):$(id -g)" node npm run build
docker compose exec -T app php artisan route:list --path=voting --path=statistics
```

Expected: PHPUnit proves backend and page-route contracts, Pint leaves no changed PHP formatting, Vite produces both entry bundles, and the route list includes page routes plus the five data routes.

- [x] **Step 2: Prepare an isolated runtime smoke dataset**

Follow the Russian README exactly: copy `.env.example` if needed, start nginx/MySQL, migrate, run `storage:link`, and import the ignored source checkout. Do not run seeders or `migrate:fresh`; do not place source JPG/JSON files under version control.

```bash
docker compose up -d nginx
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan storage:link
docker compose exec -T app php artisan cars:import storage/app/import-source
```

- [x] **Step 3: Perform the browser smoke as one voting session**

At `http://localhost:8080/`, verify model options load; choose a model; verify two distinct photos and enabled labelled buttons; activate each image zoom and observe the Tint overlay; click one side once; verify controls disable during the request and the next pair replaces the old pair without a page reload. Exercise a model with fewer than two photos if the import contains one, and disconnect/restore the network once to verify an error keeps the issued pair selectable. Use browser logs to confirm there are no JavaScript errors.

- [x] **Step 4: Perform the browser smoke for statistics and responsiveness**

At `http://localhost:8080/statistics`, verify all-model initial results, total votes, car image/data/vote count, and no reload while applying model and inclusive year filters. Verify reset reloads unfiltered data; use a nonmatching valid filter to see the empty state; try `from > to` to see client validation. At desktop width confirm the table and side-by-side voting panels; at a mobile viewport confirm cards and vertically stacked voting panels, visible controls, and no horizontal page overflow.

- [x] **Step 5: Update the delivery documentation with evidence only**

In Russian `README.md`, document `/` and `/statistics` pages, JSON endpoints `/voting/models`, `/voting/pair`, `/voting/votes`, `/statistics/models`, and `/statistics/data`, plus Vite dev/build commands. In `DECISIONS.md`, record why `/statistics/data` is separate from the Blade page and why statistics models are not sourced from the voting selector. Record exact commands/results, browser checks, skills/MCP usage, and any environmental block in `AI_WORKFLOW.md`; tick only proven requirements in `FEATURE_CHECKLIST.md`; make `HANDOFF.md` state the final verification status and the next allowed action.

- [x] **Step 6: Perform delivery hygiene checks**

Run:

```bash
git status --short
git diff --check
rg -n --hidden --glob '!.git/**' --glob '!vendor/**' --glob '!node_modules/**' --glob '!storage/app/import-source/**' --glob '!.env.example' '(BEGIN [A-Z ]*PRIVATE KEY|AWS_SECRET_ACCESS_KEY=|DB_PASSWORD=)' .
```

Expected: no unreviewed whitespace errors, source import payloads, copied public images, `.env`, credentials, tokens, or private keys are tracked. Investigate every scan match before declaring the stage complete.

## Execution Handoff

Plan saved to `.agents/plans/fordewind-laravel-design.md`.

1. **Task-by-task execution (recommended):** use `executing-plans`; begin at Task 7 and stop at each test/build gate to review its diff.
2. **Delegated execution:** only if the developer explicitly enables it, use `subagent-driven-development` with one isolated task at a time and review before the next task.
