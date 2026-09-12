<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cars\Contracts\CarPhotoStorage;
use App\Domain\Cars\Contracts\CarRepository;
use App\Domain\Cars\Data\ImportedCarDto;
use App\Domain\Cars\Data\ImportResultDto;
use JsonException;
use RuntimeException;

final class CarImportService
{
    public function __construct(private CarRepository $cars, private CarPhotoStorage $photos) {}

    public function import(string $sourcePath): ImportResultDto
    {
        if (!is_dir($sourcePath)) {
            throw new RuntimeException("Source directory not found: {$sourcePath}");
        }

        $createdCars = 0;
        $updatedCars = 0;
        $importedPhotos = 0;

        foreach (glob($sourcePath . DIRECTORY_SEPARATOR . '*.json') ?: [] as $jsonPath) {
            try {
                /** @var array<string, mixed> $payload */
                $payload = json_decode((string) file_get_contents($jsonPath), true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException(
                    "Invalid JSON file: {$jsonPath}",
                    previous: $exception
                );
            }

            $data = ImportedCarDto::fromPayload($payload);
            $upsertedCar = $this->cars->upsert(
                $data,
                $this->photos->store($sourcePath, $data->sourceFilename),
            );

            if ($upsertedCar->wasCreated) {
                $createdCars++;
            } else {
                $updatedCars++;
            }

            $importedPhotos++;
        }

        return new ImportResultDto(
            $createdCars,
            $updatedCars,
            $importedPhotos
        );
    }
}
