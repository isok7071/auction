<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Contracts;

use App\Domain\Statistics\Data\StatisticsFiltersDto;
use Illuminate\Support\Collection;

interface StatisticsRepository
{
    public function find(StatisticsFiltersDto $filters): Collection;
}
