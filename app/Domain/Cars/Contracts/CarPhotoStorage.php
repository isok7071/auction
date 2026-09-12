<?php

declare(strict_types=1);

namespace App\Domain\Cars\Contracts;

use App\Domain\Cars\Data\StoredPhotoDto;

interface CarPhotoStorage
{
    public function store(
        string $sourcePath,
        string $sourceFilename
    ): StoredPhotoDto;
}
