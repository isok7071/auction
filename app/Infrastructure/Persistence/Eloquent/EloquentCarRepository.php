<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Cars\Contracts\CarRepository;
use App\Domain\Cars\Data\ImportedCarDto;
use App\Domain\Cars\Data\StoredPhotoDto;
use App\Domain\Cars\Data\UpsertedCarDto;
use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Support\Facades\DB;

final class EloquentCarRepository implements CarRepository
{
    public function upsert(ImportedCarDto $carData, StoredPhotoDto $photo): UpsertedCarDto
    {
        return DB::transaction(function () use ($carData, $photo): UpsertedCarDto {
            $car = Car::query()->updateOrCreate(
                [Car::FIELD_SOURCE_AUCTION_ITEM_ID => $carData->sourceAuctionItemId],
                [
                    Car::FIELD_AUCTION_ID   => $carData->auctionId,
                    Car::FIELD_MAKE         => $carData->make,
                    Car::FIELD_MODEL        => $carData->model,
                    Car::FIELD_YEAR         => $carData->year,
                    Car::FIELD_ODOMETER     => $carData->odometer,
                    Car::FIELD_UNITS        => $carData->units,
                    Car::FIELD_ENGINE       => $carData->engine,
                    Car::FIELD_TRANSMISSION => $carData->transmission,
                    Car::FIELD_COLOR        => $carData->color,
                ],
            );

            CarPhoto::query()->updateOrCreate(
                [CarPhoto::FIELD_SOURCE_FILENAME => $carData->sourceFilename],
                [
                    CarPhoto::FIELD_CAR_ID       => $car->getKey(),
                    CarPhoto::FIELD_STORAGE_PATH => $photo->storagePath,
                    CarPhoto::FIELD_CHECKSUM     => $photo->checksum,
                ],
            );

            return new UpsertedCarDto($car->wasRecentlyCreated);
        });
    }
}
