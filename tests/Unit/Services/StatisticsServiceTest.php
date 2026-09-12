<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_won_votes_for_the_exact_model_and_inclusive_year_range(): void
    {
        $matchingCar = $this->carWithPhoto('FORD', 'MUSTANG', 2005);
        $zeroVoteCar = $this->carWithPhoto('FORD', 'MUSTANG', 2010);
        $this->addWonVotes($matchingCar, 3);

        $outsideModel = $this->carWithPhoto('TOYOTA', 'CAMRY', 2005);
        $this->addWonVotes($outsideModel, 2);
        $outsideYear = $this->carWithPhoto('FORD', 'MUSTANG', 2004);
        $this->addWonVotes($outsideYear, 2);

        $result = app(StatisticsService::class)->forFilters('FORD MUSTANG', 2005, 2010);

        $this->assertSame(3, $result->totalVotes);
        $this->assertSame(
            [$matchingCar->getKey(), $zeroVoteCar->getKey()],
            $result->cars->pluck(Car::FIELD_ID)->all(),
        );
        $this->assertSame([3, 0], $result->cars->pluck('votes_count')->all());
    }

    private function carWithPhoto(string $make, string $model, int $year): Car
    {
        $car = Car::factory()->create([
            Car::FIELD_MAKE  => $make,
            Car::FIELD_MODEL => $model,
            Car::FIELD_YEAR  => $year,
        ]);
        CarPhoto::factory()->for($car)->create();

        return $car;
    }

    private function addWonVotes(Car $winnerCar, int $count): void
    {
        $winnerPhoto = $winnerCar->photos()->firstOrFail();

        Vote::factory()->count($count)->create([
            Vote::FIELD_WINNER_PHOTO_ID => $winnerPhoto->getKey(),
            Vote::FIELD_WINNER_CAR_ID   => $winnerCar->getKey(),
        ]);
    }
}
