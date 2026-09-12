<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class VotingPairEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_endpoint_lists_only_models_with_two_photos(): void
    {
        $firstEligible = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        $secondEligible = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->for($firstEligible)->create();
        CarPhoto::factory()->for($secondEligible)->create();

        $ineligible = Car::factory()->create([
            Car::FIELD_MAKE  => 'TOYOTA',
            Car::FIELD_MODEL => 'CAMRY',
        ]);
        CarPhoto::factory()->for($ineligible)->create();

        $response = $this->getJson('/voting/models');

        $response->assertOk()
            ->assertJsonPath('data.0.key', 'FORD MUSTANG')
            ->assertJsonCount(1, 'data');
    }

    public function test_pair_endpoint_returns_a_pending_pair_with_public_photo_urls(): void
    {
        Storage::fake('public');
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->count(2)->for($car)->create();

        $response = $this->getJson('/voting/pair?model=FORD%20MUSTANG');

        $response->assertOk()
            ->assertJsonPath('data.model', 'FORD MUSTANG')
            ->assertJsonStructure(['data' => ['left' => ['id', 'car_id', 'url'], 'right' => ['id', 'car_id', 'url']]]);
        $this->assertStringContainsString('/storage/cars/', $response->json('data.left.url'));
    }

    public function test_pair_endpoint_returns_not_enough_photos_for_a_known_single_photo_model(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
        ]);
        CarPhoto::factory()->for($car)->create();

        $this->getJson('/voting/pair?model=FORD%20MUSTANG')
            ->assertOk()
            ->assertJsonPath('data', null)
            ->assertJsonPath('meta.reason', 'not_enough_photos');
    }

    public function test_pair_endpoint_rejects_missing_or_unknown_models(): void
    {
        $this->getJson('/voting/pair')->assertUnprocessable();
        $this->getJson('/voting/pair?model=NOT%20IMPORTED')->assertUnprocessable();
    }
}
