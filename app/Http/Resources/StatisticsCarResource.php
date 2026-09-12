<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Cars\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsCarResource extends JsonResource
{
    /**
     * @return array{id: int, make: string, model: string, model_label: string, year: int, odometer: int|null, units: string|null, engine: string|null, transmission: string|null, color: string|null, photo: array{id: int, car_id: int, url: string}|null, votes_count: int}
     */
    public function toArray(Request $request): array
    {
        /** @var Car $car */
        $car = $this->resource;
        $photo = $car->photos->first();

        return [
            'id'           => (int) $car->getKey(),
            'make'         => (string) $car->getAttribute(Car::FIELD_MAKE),
            'model'        => (string) $car->getAttribute(Car::FIELD_MODEL),
            'model_label'  => trim(
                $car->getAttribute(Car::FIELD_MAKE) . ' ' . $car->getAttribute(Car::FIELD_MODEL),
            ),
            'year'         => (int) $car->getAttribute(Car::FIELD_YEAR),
            'odometer'     => $car->getAttribute(Car::FIELD_ODOMETER),
            'units'        => $car->getAttribute(Car::FIELD_UNITS),
            'engine'       => $car->getAttribute(Car::FIELD_ENGINE),
            'transmission' => $car->getAttribute(Car::FIELD_TRANSMISSION),
            'color'        => $car->getAttribute(Car::FIELD_COLOR),
            'photo'        => $photo === null ? null : (new CarPhotoResource($photo))->toArray($request),
            'votes_count'  => (int) $car->getAttribute('votes_count'),
        ];
    }
}
