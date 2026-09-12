<?php

declare(strict_types=1);

namespace App\Domain\Cars\Data;

final readonly class ImportResultDto
{
    public function __construct(
        public int $createdCars,
        public int $updatedCars,
        public int $importedPhotos
    ) {}
}
