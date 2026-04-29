<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:50', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'O texto é obrigatório.',
            'text.min'      => 'O texto deve ter pelo menos 50 caracteres para análise.',
            'text.max'      => 'O texto não pode ter mais de 10.000 caracteres.',
        ];
    }
}
