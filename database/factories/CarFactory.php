<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Cars\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Car> */
final class CarFactory extends Factory
{
    public function definition(): array
    {
        $sourceAuctionItemId = fake()->unique()->numerify('######');

        return [
            Car::FIELD_SOURCE_AUCTION_ITEM_ID => $sourceAuctionItemId,
            Car::FIELD_AUCTION_ID             => fake()->numerify('######'),
            Car::FIELD_MAKE                   => 'FORD',
            Car::FIELD_MODEL                  => 'MUSTANG',
            Car::FIELD_YEAR                   => fake()->numberBetween(1990, 2020),
            Car::FIELD_ODOMETER               => fake()->numberBetween(0, 300000),
            Car::FIELD_UNITS                  => 'Km',
            Car::FIELD_ENGINE                 => 'GAS',
            Car::FIELD_TRANSMISSION           => 'Auto',
            Car::FIELD_COLOR                  => 'BLACK',
        ];
    }
}
