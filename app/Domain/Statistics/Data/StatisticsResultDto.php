<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Data;

use Illuminate\Pagination\LengthAwarePaginator;

final readonly class StatisticsResultDto
{
    public function __construct(
        public LengthAwarePaginator $cars,
        public int $totalVotes,
    ) {}
}
