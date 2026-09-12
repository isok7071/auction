<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Statistics\Contracts\StatisticsRepository;
use App\Domain\Statistics\Data\StatisticsFiltersDto;
use Illuminate\Support\Collection;

final class EloquentStatisticsRepository implements StatisticsRepository
{
    public function find(StatisticsFiltersDto $filters): Collection
    {
        $query = Car::query()
            ->with([
                'photos' => fn($query) => $query
                    ->orderBy(CarPhoto::FIELD_ID)
                    ->limit(1),
            ])
            ->withCount('wonVotes as votes_count');

        if ($filters->modelKey !== null) {
            $query->whereModelKey($filters->modelKey);
        }

        if ($filters->yearFrom !== null) {
            $query->where(Car::FIELD_YEAR, '>=', $filters->yearFrom);
        }

        if ($filters->yearTo !== null) {
            $query->where(Car::FIELD_YEAR, '<=', $filters->yearTo);
        }

        return $query
            ->orderByDesc('votes_count')
            ->orderBy(Car::FIELD_MAKE)
            ->orderBy(Car::FIELD_MODEL)
            ->orderBy(Car::FIELD_YEAR)
            ->orderBy(Car::FIELD_ID)
            ->get();
    }
}
