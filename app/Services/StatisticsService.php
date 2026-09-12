<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Statistics\Contracts\StatisticsRepository;
use App\Domain\Statistics\Data\StatisticsFiltersDto;
use App\Domain\Statistics\Data\StatisticsResultDto;
use Illuminate\Support\Collection;

final class StatisticsService
{
    public function __construct(private StatisticsRepository $statistics) {}

    public function models(): Collection
    {
        return $this->statistics->modelOptions();
    }

    public function forFilters(?string $modelKey, ?int $yearFrom, ?int $yearTo): StatisticsResultDto
    {
        $cars = $this->statistics->find(new StatisticsFiltersDto($modelKey, $yearFrom, $yearTo));

        return new StatisticsResultDto($cars, (int) $cars->sum('votes_count'));
    }
}
