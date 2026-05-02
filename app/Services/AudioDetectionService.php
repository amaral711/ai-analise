<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AudioDetectionService
{
    private string $url;
    private ?string $token;

    public function __construct()
    {
        $this->url   = config('services.audio_analysis.url', 'http://audio_analysis:8002/analyze');
        $this->token = config('services.audio_analysis.token');
    }

    public function analyzeAudio(UploadedFile $file): array
    {
        Log::info('Audio AI analysis start', ['url' => $this->url, 'file' => $file->getClientOriginalName()]);

        try {
            $request = Http::timeout(60);

            if ($this->token) {
                $request = $request->withToken($this->token);
            }

            $response = $request
                ->attach('file', file_get_contents($file->path()), $file->getClientOriginalName())
                ->post($this->url);

            Log::info('Audio AI response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                return $this->buildResult($response->json());
            }

            if ($response->status() === 400 || $response->status() === 422) {
                throw new \InvalidArgumentException($response->json('detail') ?? 'Erro ao analisar áudio.');
            }

            Log::warning('Python AI audio service error', ['status' => $response->status()]);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning('Python AI audio service unavailable', ['error' => $e->getMessage()]);
        }

        throw new \RuntimeException('Serviço de análise indisponível. Tente novamente.');
    }

    private function buildResult(array $data): array
    {
        $aiScore = round((float) ($data['ai_score'] ?? 0.5), 2);

        $classification = match (true) {
            $aiScore < 0.4 => 'human',
            $aiScore < 0.7 => 'inconclusive',
            default        => 'ai',
        };

        $explanation = [];

        if ($aiScore >= 0.7) {
            $explanation[] = 'Alta probabilidade de voz gerada por síntese de IA (TTS)';
            $explanation[] = 'Padrões acústicos inconsistentes com voz humana natural';
        } elseif ($aiScore >= 0.4) {
            $explanation[] = 'Padrões mistos — áudio pode ter sido processado ou sintetizado parcialmente';
            $explanation[] = 'Características acústicas ambíguas detectadas';
        } else {
            $explanation[] = 'Padrões acústicos consistentes com voz humana natural';
            $explanation[] = 'Nenhuma marca identificável de síntese por IA';
        }

        $model = $data['model'] ?? 'desconhecido';
        $explanation[] = "Modelo utilizado: {$model}";

        return [
            'ai_score'       => $aiScore,
            'classification' => $classification,
            'explanation'    => $explanation,
        ];
    }
}
