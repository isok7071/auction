<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ShowVotingPairRequest extends FormRequest
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
        return ['model' => ['required', 'string', 'max:255']];
    }
}
