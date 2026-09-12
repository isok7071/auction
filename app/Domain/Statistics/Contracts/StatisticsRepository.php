<?php

declare(strict_types=1);

namespace App\Domain\Statistics\Contracts;

use App\Domain\Statistics\Data\StatisticsFiltersDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface StatisticsRepository
{
    public function modelOptions(): Collection;

    public function paginate(StatisticsFiltersDto $filters, int $page, int $perPage): LengthAwarePaginator;

    public function totalVotes(StatisticsFiltersDto $filters): int;
}
