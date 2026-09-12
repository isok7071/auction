<?php

declare(strict_types=1);

namespace App\Domain\Cars\Data;

final readonly class ImportSourceDto
{
    public function __construct(
        public string $path,
        public string $temporaryDirectory
    ) {}
}
