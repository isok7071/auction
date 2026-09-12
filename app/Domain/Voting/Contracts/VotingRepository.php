<?php

declare(strict_types=1);

namespace App\Domain\Voting\Contracts;

use Illuminate\Support\Collection;

interface VotingRepository
{
    public function modelOptions(): Collection;

    public function hasModel(string $modelKey): bool;

    public function photoIdsForModel(string $modelKey): Collection;

    public function photosByIds(array $photoIds): Collection;
}
