<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Contracts\VoteRepository;
use App\Domain\Voting\Data\RecordVoteDto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class EloquentVoteRepository implements VoteRepository
{
    public function findPhotosWithCars(array $photoIds): Collection
    {
        return CarPhoto::query()
            ->with('car')
            ->whereIn(CarPhoto::FIELD_ID, $photoIds)
            ->get()
            ->keyBy(CarPhoto::FIELD_ID);
    }

    public function create(RecordVoteDto $data): Vote
    {
        return DB::transaction(fn(): Vote => Vote::query()->create([
            Vote::FIELD_SESSION_ID      => $data->sessionId,
            Vote::FIELD_WINNER_PHOTO_ID => $data->winnerPhotoId,
            Vote::FIELD_LOSER_PHOTO_ID  => $data->loserPhotoId,
            Vote::FIELD_WINNER_CAR_ID   => $data->winnerCarId,
            Vote::FIELD_LOSER_CAR_ID    => $data->loserCarId,
        ]));
    }
}
