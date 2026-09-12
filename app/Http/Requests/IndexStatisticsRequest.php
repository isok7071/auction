<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IndexStatisticsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'model' => is_string($this->input('model'))
                ? trim($this->input('model'))
                : $this->input('model'),
        ]);
    }

    public function rules(): array
    {
        $currentYear = now()->year;

        return [
            'model'     => [
                'nullable',
                'string',
                'max:255',
            ],
            'year_from' => [
                'nullable',
                'integer',
                'between:1886,' . $currentYear,
            ],
            'year_to'   => [
                'nullable',
                'integer',
                'between:1886,' . $currentYear,
                'gte:year_from',
            ],
        ];
    }
}
