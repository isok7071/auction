<?php

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
            'source_auction_item_id' => $sourceAuctionItemId,
            'auction_id' => fake()->numerify('######'),
            'make' => 'FORD',
            'model' => 'MUSTANG',
            'year' => fake()->numberBetween(1990, 2020),
            'odometer' => fake()->numberBetween(0, 300000),
            'units' => 'Km',
            'engine' => 'GAS',
            'transmission' => 'Auto',
            'color' => 'BLACK',
            'source_payload' => ['AuctionItemId' => $sourceAuctionItemId],
        ];
    }
}
