<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiDetectionService
{
    private string $pythonUrl;

    public function __construct(private ImageMetadataAnalyzer $metadataAnalyzer)
    {
        $this->pythonUrl = rtrim(config('services.python_ai.url', 'http://python-ai:8001'), '/');
    }

    public function analyzeImage(UploadedFile $file): array
    {
        Log::info('AI analysis start', ['url' => $this->pythonUrl, 'file' => $file->getClientOriginalName()]);

        $metadataObservations = $this->metadataAnalyzer->analyze($file);

        try {
            $response = Http::timeout(60)
                ->attach('file', file_get_contents($file->path()), $file->getClientOriginalName())
                ->post($this->pythonUrl . '/detect/image');

            Log::info('AI response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                return $this->buildResult($response->json(), $metadataObservations);
            }

            if ($response->status() === 400 || $response->status() === 422) {
                throw new \InvalidArgumentException($response->json('detail') ?? 'Erro ao analisar imagem.');
            }

            Log::warning('Python AI service error', ['status' => $response->status()]);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning('Python AI service unavailable', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        throw new \RuntimeException('Serviço de análise indisponível. Tente novamente.');
    }

    private function buildResult(array $data, array $metadataObservations = []): array
    {
        $aiScore = round((float) ($data['ai_score'] ?? 0.5), 2);

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

        $model = $data['model'] ?? 'desconhecido';
        $explanation[] = "Modelo utilizado: {$model}";

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
