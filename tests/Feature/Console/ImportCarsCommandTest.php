<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
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
        $this->assertSame('342154', $car->getAttribute(Car::FIELD_SOURCE_AUCTION_ITEM_ID));
        $this->assertSame('TOYOTA', $car->getAttribute(Car::FIELD_MAKE));
        $this->assertSame('TUNDRA LIMITED', $car->getAttribute(Car::FIELD_MODEL));
        $this->assertSame(2003, $car->getAttribute(Car::FIELD_YEAR));
        $this->assertSame(
            hash_file('sha256', $sourcePath . '/71b32420fd9ba92739b3e06704f7b213.jpg'),
            $photo->getAttribute(CarPhoto::FIELD_CHECKSUM),
        );
        Storage::disk('public')->assertExists($photo->getAttribute(CarPhoto::FIELD_STORAGE_PATH));

        $this->artisan('cars:import', ['source' => $sourcePath])
            ->expectsOutputToContain('updated=1')
            ->assertExitCode(0);

        $this->assertDatabaseCount('cars', 1);
        $this->assertDatabaseCount('car_photos', 1);
    }

    public function test_missing_source_image_does_not_insert_car(): void
    {
        Storage::fake('public');
        $sourcePath = storage_path('framework/testing/import-missing-image-' . Str::uuid());
        File::ensureDirectoryExists($sourcePath);
        File::put($sourcePath . '/car.json', json_encode([
            'AuctionItemId' => '342154',
            'Make'          => 'TOYOTA',
            'Model'         => 'TUNDRA LIMITED',
            'Year'          => 2003,
            'Image'         => 'missing.jpg',
        ], JSON_THROW_ON_ERROR));

        try {
            $this->artisan('cars:import', ['source' => $sourcePath])->run();
            $this->fail('Import must fail when its source image is missing.');
        } catch (InvalidArgumentException) {
            $this->assertDatabaseEmpty('cars');
            $this->assertDatabaseEmpty('car_photos');
        } finally {
            File::deleteDirectory($sourcePath);
        }
    }
}
