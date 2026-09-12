<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Voting\Data\VotingPairDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class VotingPairResource extends JsonResource
{
    /**
     * @return array{model: string, left: array{id: int, car_id: int, url: string}, right: array{id: int, car_id: int, url: string}}
     */
    public function toArray(Request $request): array
    {
        /** @var VotingPairDto $pair */
        $pair = $this->resource;

        return [
            'model' => $pair->modelKey,
            'left'  => (new CarPhotoResource($pair->left))->toArray($request),
            'right' => (new CarPhotoResource($pair->right))->toArray($request),
        ];
    }
}
