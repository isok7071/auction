<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cars\Models\Car;
use App\Domain\Voting\Contracts\VotingRepository;
use App\Domain\Voting\Data\VotingPairDto;
use Illuminate\Session\Store;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

final class VotingPairService
{
    public const int PAIR_SIZE = 2;

    public function __construct(private VotingRepository $voting) {}

    public function models(): Collection
    {
        return $this->voting->modelOptions();
    }

    public function hasModel(string $modelKey): bool
    {
        return $this->voting->hasModel($modelKey);
    }

    public function pairFor(string $modelKey, Store $session): ?VotingPairDto
    {
        $pendingKey = $this->pendingKey($modelKey);
        $pending = $session->get($pendingKey);

        if (
            is_array($pending)
            && isset($pending['left'], $pending['right'])
        ) {
            $photos = $this->voting->photosByIds([(int) $pending['left'], (int) $pending['right']]);

            if ($photos->count() === self::PAIR_SIZE && $this->photosMatchModel($photos, $modelKey)) {
                return new VotingPairDto(
                    $modelKey,
                    $photos->get((int) $pending['left']),
                    $photos->get((int) $pending['right']),
                );
            }

            $session->forget($pendingKey);
        }

        $photoIds = $this->voting->photoIdsForModel($modelKey)->map(static fn(mixed $id): int => (int) $id)->all();

        if (count($photoIds) < self::PAIR_SIZE) {
            return null;
        }

        $deckKey = $this->deckKey($modelKey);
        $deck = array_values(array_intersect(
            array_map(static fn(mixed $id): int => (int) $id, (array) $session->get($deckKey, [])),
            $photoIds,
        ));

        if (count($deck) >= self::PAIR_SIZE) {
            shuffle($deck);
            $pairIds = [array_shift($deck), array_shift($deck)];
        } elseif (count($deck) === 1) {
            $unseenId = (int) array_shift($deck);
            $nextDeck = array_values(array_diff($photoIds, [$unseenId]));
            shuffle($nextDeck);
            $pairIds = [$unseenId, (int) array_shift($nextDeck)];
            $deck = array_values(array_diff($nextDeck, [$pairIds[1]]));
        } else {
            $deck = $photoIds;
            shuffle($deck);
            $pairIds = [array_shift($deck), array_shift($deck)];
        }

        $session->put($deckKey, $deck);
        $session->put($pendingKey, ['left' => $pairIds[0], 'right' => $pairIds[1]]);

        $photos = $this->voting->photosByIds($pairIds);

        if ($photos->count() !== self::PAIR_SIZE || !$this->photosMatchModel($photos, $modelKey)) {
            $session->forget($pendingKey);

            throw ValidationException::withMessages([
                'model' => 'The selected model is no longer available.',
            ]);
        }

        return new VotingPairDto($modelKey, $photos->get($pairIds[0]), $photos->get($pairIds[1]));
    }

    public function clearPendingPair(string $modelKey, Store $session): void
    {
        $session->forget($this->pendingKey($modelKey));
    }

    /**
     * @return array{left: int, right: int}|null
     */
    public function pendingPair(string $modelKey, Store $session): ?array
    {
        $pending = $session->get($this->pendingKey($modelKey));

        if (!is_array($pending) || !isset($pending['left'], $pending['right'])) {
            return null;
        }

        return [
            'left'  => (int) $pending['left'],
            'right' => (int) $pending['right'],
        ];
    }

    private function photosMatchModel(Collection $photos, string $modelKey): bool
    {
        foreach ($photos as $photo) {
            $car = $photo->car;
            $actualKey = trim(
                $car->getAttribute(Car::FIELD_MAKE) . ' ' . $car->getAttribute(Car::FIELD_MODEL),
            );

            if ($actualKey !== $modelKey) {
                return false;
            }
        }

        return true;
    }

    private function deckKey(string $modelKey): string
    {
        return 'voting.decks.' . sha1($modelKey);
    }

    private function pendingKey(string $modelKey): string
    {
        return 'voting.pending_pairs.' . sha1($modelKey);
    }
}
