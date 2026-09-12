<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Voting\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class VoteResource extends JsonResource
{
    /**
     * @return array{id: int, winner_photo_id: int, loser_photo_id: int}
     */
    public function toArray(Request $request): array
    {
        /** @var Vote $vote */
        $vote = $this->resource;

        return [
            'id'              => (int) $vote->getKey(),
            'winner_photo_id' => (int) $vote->getAttribute(Vote::FIELD_WINNER_PHOTO_ID),
            'loser_photo_id'  => (int) $vote->getAttribute(Vote::FIELD_LOSER_PHOTO_ID),
        ];
    }
}
