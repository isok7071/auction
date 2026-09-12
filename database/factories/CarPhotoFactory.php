<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarPhoto> */
final class CarPhotoFactory extends Factory
{
    public function definition(): array
    {
        $filename = fake()->unique()->sha256() . '.jpg';

        return [
            CarPhoto::FIELD_CAR_ID          => Car::factory(),
            CarPhoto::FIELD_SOURCE_FILENAME => $filename,
            CarPhoto::FIELD_STORAGE_PATH    => 'cars/' . $filename,
            CarPhoto::FIELD_CHECKSUM        => hash('sha256', $filename),
        ];
    }
}
