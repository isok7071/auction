<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Cars\Models\CarPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CarPhotoResource extends JsonResource
{
    /**
     * @return array{id: int, car_id: int, url: string}
     */
    public function toArray(Request $request): array
    {
        /** @var CarPhoto $photo */
        $photo = $this->resource;

        return [
            'id'     => (int) $photo->getKey(),
            'car_id' => (int) $photo->getAttribute(CarPhoto::FIELD_CAR_ID),
            'url'    => $photo->url(),
        ];
    }
}
