<?php

namespace Database\Factories;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vote> */
final class VoteFactory extends Factory
{
    public function definition(): array
    {
        $winnerCar = Car::factory();
        $loserCar = Car::factory();

        return [
            'session_id' => fake()->uuid(),
            'winner_photo_id' => CarPhoto::factory()->for($winnerCar),
            'loser_photo_id' => CarPhoto::factory()->for($loserCar),
            'winner_car_id' => $winnerCar,
            'loser_car_id' => $loserCar,
        ];
    }
}
