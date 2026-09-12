<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ImportCarsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_source_car_and_photo_idempotently(): void
    {
        Storage::fake('public');
        $sourcePath = base_path('tests/Fixtures/import-source');

        $this->artisan('cars:import', ['source' => $sourcePath])
            ->expectsOutputToContain('created=1')
            ->assertExitCode(0);

        $car = Car::query()->sole();
        $photo = CarPhoto::query()->sole();
        $this->assertSame('342154', $car->source_auction_item_id);
        $this->assertSame('TOYOTA', $car->make);
        $this->assertSame('TUNDRA LIMITED', $car->model);
        $this->assertSame(2003, $car->year);
        Storage::disk('public')->assertExists($photo->storage_path);

        $this->artisan('cars:import', ['source' => $sourcePath])
            ->expectsOutputToContain('updated=1')
            ->assertExitCode(0);

        $this->assertDatabaseCount('cars', 1);
        $this->assertDatabaseCount('car_photos', 1);
    }
}
