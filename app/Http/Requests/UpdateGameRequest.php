<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'home_score' => ['nullable', 'integer', 'min:0'],
            'away_score' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'home_score.integer' => 'Home score must be an integer.',
            'home_score.min' => 'Home score cannot be negative.',
            'away_score.integer' => 'Away score must be an integer.',
            'away_score.min' => 'Away score cannot be negative.',
        ];
    }
}
