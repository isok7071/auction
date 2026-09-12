<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StatisticsEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_filtered_cars_and_total_votes(): void
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => 'FORD',
            Car::FIELD_MODEL => 'MUSTANG',
            Car::FIELD_YEAR  => 2005,
        ]);
        $photo = CarPhoto::factory()->for($car)->create();
        Vote::factory()->count(2)->create([
            Vote::FIELD_WINNER_PHOTO_ID => $photo->getKey(),
            Vote::FIELD_WINNER_CAR_ID   => $car->getKey(),
        ]);

        $response = $this->getJson('/statistics?model=FORD%20MUSTANG&year_from=2005&year_to=2005');

        $response
            ->assertOk()
            ->assertJsonPath('meta.total_votes', 2)
            ->assertJsonPath('data.0.id', $car->getKey())
            ->assertJsonPath('data.0.votes_count', 2)
            ->assertJsonStructure(['data' => [['photo' => ['id', 'car_id', 'url']]]]);
        $this->assertStringContainsString('/storage/cars/', $response->json('data.0.photo.url'));
    }

    public function test_it_rejects_an_inverted_year_range(): void
    {
        $this->getJson('/statistics?year_from=2010&year_to=2005')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['year_to']);
    }

    public function test_it_includes_zero_vote_cars_and_a_public_photo_url_when_unfiltered(): void
    {
        $car = Car::factory()->create();
        CarPhoto::factory()->for($car)->create();

        $this->getJson('/statistics')
            ->assertOk()
            ->assertJsonPath('data.0.id', $car->getKey())
            ->assertJsonPath('data.0.votes_count', 0)
            ->assertJsonPath('meta.total_votes', 0);
    }

    public function test_it_returns_an_empty_collection_with_zero_total_for_non_matching_filters(): void
    {
        $this->getJson('/statistics?model=NOT%20IMPORTED')
            ->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total_votes', 0);
    }
}
