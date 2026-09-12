<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Data;

final readonly class StatisticsFiltersDto
{
    public function __construct(
        public ?string $modelKey,
        public ?int $yearFrom,
        public ?int $yearTo,
    ) {}
}
