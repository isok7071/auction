<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Statistics\Contracts\StatisticsRepository;
use App\Domain\Statistics\Data\StatisticsFiltersDto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class EloquentStatisticsRepository implements StatisticsRepository
{
    public function modelOptions(): Collection
    {
        return Car::query()
            ->selectModelKey()
            ->groupBy(Car::FIELD_MAKE, Car::FIELD_MODEL)
            ->orderBy(Car::FIELD_MAKE)
            ->orderBy(Car::FIELD_MODEL)
            ->get();
    }

    public function paginate(StatisticsFiltersDto $filters, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->filteredCarsQuery($filters)
            ->with([
                'photos' => fn($query) => $query
                    ->orderBy(CarPhoto::FIELD_ID)
                    ->limit(1),
            ])
            ->withCount('wonVotes as votes_count')
            ->orderByDesc('votes_count')
            ->orderBy(Car::FIELD_MAKE)
            ->orderBy(Car::FIELD_MODEL)
            ->orderBy(Car::FIELD_YEAR)
            ->orderBy(Car::FIELD_ID)
            ->paginate(perPage: $perPage, page: $page);
    }

    public function totalVotes(StatisticsFiltersDto $filters): int
    {
        return Vote::query()
            ->whereIn(
                Vote::FIELD_WINNER_CAR_ID,
                $this->filteredCarsQuery($filters)->select(Car::FIELD_ID),
            )
            ->count();
    }

    private function filteredCarsQuery(StatisticsFiltersDto $filters): Builder
    {
        $query = Car::query();

        if ($filters->modelKey !== null) {
            $query->whereModelKey($filters->modelKey);
        }

        if ($filters->yearFrom !== null) {
            $query->where(Car::FIELD_YEAR, '>=', $filters->yearFrom);
        }

        if ($filters->yearTo !== null) {
            $query->where(Car::FIELD_YEAR, '<=', $filters->yearTo);
        }

        return $query;
    }
}
