<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogStatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'log_text' => ['required', 'string'],
            'keyword_groups' => ['required', 'array', 'min:1'],
            'keyword_groups.*' => ['required', 'array', 'min:1'],
            'keyword_groups.*.*' => ['required', 'string', 'min:1'],
        ];
    }
}
