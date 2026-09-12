<?php

declare(strict_types=1);

namespace App\Domain\Cars\Contracts;

use App\Domain\Cars\Data\ImportedCarDto;
use App\Domain\Cars\Data\StoredPhotoDto;
use App\Domain\Cars\Data\UpsertedCarDto;

interface CarRepository
{
    public function upsert(
        ImportedCarDto $carData,
        StoredPhotoDto $photo
    ): UpsertedCarDto;
}
