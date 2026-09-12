<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Data;

use Illuminate\Support\Collection;

final readonly class StatisticsResultDto
{
    public function __construct(
        public Collection $cars,
        public int $totalVotes,
    ) {}
}
