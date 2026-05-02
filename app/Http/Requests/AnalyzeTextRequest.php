<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:100', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'O texto é obrigatório.',
            'content.min'      => 'O texto deve ter pelo menos 100 caracteres.',
            'content.max'      => 'O texto não pode ter mais de 5.000 caracteres.',
        ];
    }
}
