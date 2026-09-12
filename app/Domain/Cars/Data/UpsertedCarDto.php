<?php

declare(strict_types=1);

namespace App\Domain\Cars\Data;

final readonly class UpsertedCarDto
{
    public function __construct(public bool $wasCreated) {}
}
