<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class StatisticsModelResource extends JsonResource
{
    /**
     * @return array{key: string, label: string}
     */
    public function toArray(Request $request): array
    {
        $key = (string) $this->resource->getAttribute('model_key');

        return ['key' => $key, 'label' => $key];
    }
}
