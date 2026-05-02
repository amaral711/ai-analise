<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnalyzeAudioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audio' => ['required', 'file', 'mimes:mp3,wav,ogg,m4a,aac', 'max:25600'],
        ];
    }

    public function messages(): array
    {
        return [
            'audio.required' => 'Selecione um arquivo de áudio.',
            'audio.file'     => 'O arquivo deve ser um áudio válido.',
            'audio.mimes'    => 'O áudio deve ser MP3, WAV, OGG ou M4A/AAC.',
            'audio.max'      => 'O áudio não pode ter mais de 25 MB.',
        ];
    }
}
