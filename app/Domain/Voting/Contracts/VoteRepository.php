<?php

declare(strict_types=1);

namespace App\Domain\Voting\Contracts;

use App\Domain\Voting\Data\RecordVoteDto;
use App\Domain\Voting\Models\Vote;
use Illuminate\Support\Collection;

interface VoteRepository
{
    public function findPhotosWithCars(array $photoIds): Collection;

    public function create(RecordVoteDto $data): Vote;
}
