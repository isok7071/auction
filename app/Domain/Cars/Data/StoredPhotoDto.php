<?php

declare(strict_types=1);

namespace App\Domain\Cars\Data;

final readonly class StoredPhotoDto
{
    public function __construct(
        public string $storagePath,
        public string $checksum
    ) {}
}
