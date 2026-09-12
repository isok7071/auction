<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Vote> */
final class VoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            Vote::FIELD_SESSION_ID      => fake()->uuid(),
            Vote::FIELD_WINNER_PHOTO_ID => CarPhoto::factory(),
            Vote::FIELD_LOSER_PHOTO_ID  => CarPhoto::factory(),
            Vote::FIELD_WINNER_CAR_ID   => static fn(array $attributes): int => (int) CarPhoto::query()
                ->findOrFail($attributes[Vote::FIELD_WINNER_PHOTO_ID])
                ->getAttribute(CarPhoto::FIELD_CAR_ID),
            Vote::FIELD_LOSER_CAR_ID    => static fn(array $attributes): int => (int) CarPhoto::query()
                ->findOrFail($attributes[Vote::FIELD_LOSER_PHOTO_ID])
                ->getAttribute(CarPhoto::FIELD_CAR_ID),
        ];
    }
}
