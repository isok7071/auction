<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Contracts\VotingRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EloquentVotingRepository implements VotingRepository
{
    public function modelOptions(): Collection
    {
        $car = new Car;
        $photo = new CarPhoto;

        return Car::query()
            ->join(
                $photo->getTable(),
                $car->qualifyColumn(Car::FIELD_ID),
                '=',
                $photo->qualifyColumn(CarPhoto::FIELD_CAR_ID),
            )
            ->select([
                $car->qualifyColumn(Car::FIELD_MAKE),
                $car->qualifyColumn(Car::FIELD_MODEL),
            ])
            ->selectModelKey()
            ->groupBy($car->qualifyColumn(Car::FIELD_MAKE), $car->qualifyColumn(Car::FIELD_MODEL))
            ->havingRaw('COUNT(' . $photo->qualifyColumn(CarPhoto::FIELD_ID) . ') >= ?', [2])
            ->orderBy($car->qualifyColumn(Car::FIELD_MAKE))
            ->orderBy($car->qualifyColumn(Car::FIELD_MODEL))
            ->get();
    }

    public function hasModel(string $modelKey): bool
    {
        return Car::query()
            ->whereModelKey($modelKey)
            ->exists();
    }

    public function photoIdsForModel(string $modelKey): Collection
    {
        return CarPhoto::query()
            ->whereHas('car', function (Builder $query) use ($modelKey): void {
                $query->whereModelKey($modelKey);
            })
            ->orderBy(CarPhoto::FIELD_ID)
            ->pluck(CarPhoto::FIELD_ID);
    }

    public function photosByIds(array $photoIds): Collection
    {
        return CarPhoto::query()
            ->with('car')
            ->whereIn(CarPhoto::FIELD_ID, $photoIds)
            ->get()
            ->keyBy(CarPhoto::FIELD_ID);
    }
}
