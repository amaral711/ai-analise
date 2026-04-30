<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Selecione uma imagem.',
            'image.image'    => 'O arquivo deve ser uma imagem.',
            'image.mimes'    => 'A imagem deve ser JPG, PNG ou WebP.',
            'image.max'      => 'A imagem não pode ter mais de 10 MB.',
        ];
    }
}
