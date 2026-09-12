<?php

declare(strict_types=1);

namespace App\Domain\Voting\Data;

use App\Domain\Cars\Models\CarPhoto;

final readonly class VotingPairDto
{
    public function __construct(
        public string $modelKey,
        public CarPhoto $left,
        public CarPhoto $right,
    ) {}
}
