<?php

namespace Database\Factories;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarPhoto> */
final class CarPhotoFactory extends Factory
{
    public function definition(): array
    {
        $filename = fake()->unique()->sha256().'.jpg';

        return [
            'car_id' => Car::factory(),
            'source_filename' => $filename,
            'storage_path' => 'cars/'.$filename,
            'checksum' => hash('sha256', $filename),
        ];
    }
}
