<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cars\Models\Car;
use App\Domain\Cars\Models\CarPhoto;
use App\Domain\Voting\Contracts\VoteRepository;
use App\Domain\Voting\Data\RecordVoteDto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Session\Store;
use Illuminate\Validation\ValidationException;

final class VoteService
{
    public function __construct(
        private VoteRepository $votes,
        private VotingPairService $pairs,
    ) {}

    public function record(
        string $modelKey,
        int $winnerPhotoId,
        int $loserPhotoId,
        Store $session
    ): Vote {
        if ($winnerPhotoId === $loserPhotoId) {
            throw ValidationException::withMessages([
                'winner_photo_id' => 'Winner and loser photos must be different.',
            ]);
        }

        $pending = $this->pairs->pendingPair(
            $modelKey,
            $session
        );

        if ($pending === null) {
            throw ValidationException::withMessages([
                'model' => 'The voting pair has expired.',
            ]);
        }

        $submittedIds = [$winnerPhotoId, $loserPhotoId];
        $pendingIds = [$pending['left'], $pending['right']];
        sort($submittedIds);
        sort($pendingIds);

        if ($submittedIds !== $pendingIds) {
            throw ValidationException::withMessages([
                'winner_photo_id' => 'The submitted photos were not issued as this pair.',
            ]);
        }

        $photos = $this->votes->findPhotosWithCars([$winnerPhotoId, $loserPhotoId]);

        if ($photos->count() !== VotingPairService::PAIR_SIZE) {
            throw ValidationException::withMessages([
                'winner_photo_id' => 'One or more selected photos no longer exist.',
            ]);
        }

        foreach ($photos as $photo) {
            $car = $photo->car;
            $photoModelKey = trim(
                $car->getAttribute(Car::FIELD_MAKE) . ' ' . $car->getAttribute(Car::FIELD_MODEL),
            );

            if ($photoModelKey !== $modelKey) {
                throw ValidationException::withMessages([
                    'model' => 'The selected photos do not belong to this model.',
                ]);
            }
        }

        $winnerPhoto = $photos->get($winnerPhotoId);
        $loserPhoto = $photos->get($loserPhotoId);
        $vote = $this->votes->create(new RecordVoteDto(
            (string) $session->getId(),
            $winnerPhotoId,
            $loserPhotoId,
            (int) $winnerPhoto->getAttribute(CarPhoto::FIELD_CAR_ID),
            (int) $loserPhoto->getAttribute(CarPhoto::FIELD_CAR_ID),
        ));

        $this->pairs->clearPendingPair($modelKey, $session);

        return $vote;
    }
}
