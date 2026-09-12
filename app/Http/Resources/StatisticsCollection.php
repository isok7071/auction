<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

final class StatisticsCollection extends ResourceCollection
{
    public $collects = StatisticsCarResource::class;

    public function __construct(LengthAwarePaginator $resource, private int $totalVotes)
    {
        parent::__construct($resource);
    }

    /**
     * @return array{meta: array{total_votes: int}}
     */
    public function with(Request $request): array
    {
        return ['meta' => ['total_votes' => $this->totalVotes]];
    }
}
