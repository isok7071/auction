<?php

declare(strict_types=1);

namespace App\Domain\Voting\Data;

final readonly class RecordVoteDto
{
    public function __construct(
        public string $sessionId,
        public int $winnerPhotoId,
        public int $loserPhotoId,
        public int $winnerCarId,
        public int $loserCarId,
    ) {}
}
