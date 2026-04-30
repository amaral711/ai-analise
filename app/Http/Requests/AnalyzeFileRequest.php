<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf,docx,txt', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Selecione um arquivo.',
            'document.mimes'    => 'O arquivo deve ser PDF, DOCX ou TXT.',
            'document.max'      => 'O arquivo não pode ter mais de 10 MB.',
        ];
    }
}
