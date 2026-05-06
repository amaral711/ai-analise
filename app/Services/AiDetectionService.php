<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiDetectionService
{
    private string $url;
    private ?string $token;

    public function __construct(private ImageMetadataAnalyzer $metadataAnalyzer)
    {
        $this->url   = config('services.image_analysis.url');
        $this->token = config('services.image_analysis.token');
    }

    public function analyzeImage(UploadedFile $file): array
    {
        Log::info('AI analysis start', ['url' => $this->url, 'file' => $file->getClientOriginalName()]);

        $metadataObservations = $this->metadataAnalyzer->analyze($file);

        // HF Spaces gratuitos dormem após inatividade — cold start pode levar 90s
        // Tentativa 1 com timeout curto; se falhar por timeout, tenta novamente com mais tempo
        $attempts = [60, 90];

        foreach ($attempts as $i => $timeout) {
            try {
                $request = Http::timeout($timeout);

                if ($this->token) {
                    $request = $request->withToken($this->token);
                }

                $response = $request
                    ->attach('file', file_get_contents($file->path()), $file->getClientOriginalName())
                    ->post($this->url);

                Log::info('AI response', ['status' => $response->status(), 'attempt' => $i + 1]);

                if ($response->successful()) {
                    return $this->buildResult($response->json(), $metadataObservations);
                }

                if ($response->status() === 400 || $response->status() === 422) {
                    throw new \InvalidArgumentException($response->json('detail') ?? 'Erro ao analisar imagem.');
                }

                Log::warning('Image AI service error', ['status' => $response->status()]);
                break;
            } catch (\InvalidArgumentException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::warning('Image AI attempt failed', ['attempt' => $i + 1, 'error' => $e->getMessage()]);

                if ($i === array_key_last($attempts)) {
                    break;
                }
            }
        }

        throw new \RuntimeException('Serviço de análise indisponível. Tente novamente.');
    }

    private function buildResult(array $data, array $metadataObservations = []): array
    {
        $aiScore = round((float) ($data['probabilidade_ia'] ?? $data['ai_score'] ?? 0.5), 2);

        $classification = match (true) {
            $aiScore < 0.4 => 'human',
            $aiScore < 0.7 => 'inconclusive',
            default        => 'ai',
        };

        $explanation = [];

        if ($aiScore >= 0.7) {
            $explanation[] = 'Alta probabilidade de geração por modelo de IA generativa';
            $explanation[] = 'Padrões visuais inconsistentes com fotografia real';
        } elseif ($aiScore >= 0.4) {
            $explanation[] = 'Padrões mistos — imagem pode ter sido editada após geração por IA';
            $explanation[] = 'Características ambíguas detectadas';
        } else {
            $explanation[] = 'Padrões visuais consistentes com fotografia real';
            $explanation[] = 'Nenhuma marca identificável de geração por IA';
        }

        $model = $data['modelo_usado'] ?? $data['model'] ?? 'desconhecido';
        $explanation[] = "Modelo utilizado: {$model}";

        if (isset($data['detalhes'])) {
            $scoreModelo = $data['detalhes']['score_modelo'] ?? null;
            $scoreFft    = $data['detalhes']['score_fft'] ?? null;
            if ($scoreModelo !== null && $scoreFft !== null) {
                $explanation[] = "Score classificador: {$scoreModelo} | Score FFT: {$scoreFft}";
            }
        }

        if (!empty($data['aviso'])) {
            $explanation[] = "⚠️ {$data['aviso']}";
        }

        foreach ($metadataObservations as $observation) {
            $explanation[] = "⚠️ Observação: {$observation}";
        }

        return [
            'ai_score'       => $aiScore,
            'classification' => $classification,
            'explanation'    => $explanation,
        ];
    }
}
