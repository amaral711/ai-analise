<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextDetectionService
{
    private string $pythonUrl;

    public function __construct()
    {
        $this->pythonUrl = rtrim(config('services.python_ai.url', 'http://python-ai:8001'), '/');
    }

    public function analyzeText(string $text): array
    {
        Log::info('Text AI analysis start', ['url' => $this->pythonUrl, 'length' => strlen($text)]);

        try {
            $response = Http::timeout(60)
                ->acceptJson()
                ->post($this->pythonUrl . '/detect/text', ['text' => $text]);

            Log::info('Text AI response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                return $this->buildResult($response->json());
            }

            if ($response->status() === 400 || $response->status() === 422) {
                throw new \InvalidArgumentException($response->json('detail') ?? 'Erro ao analisar texto.');
            }

            Log::warning('Python AI text service error', ['status' => $response->status()]);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning('Python AI text service unavailable', ['error' => $e->getMessage()]);
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
            $explanation[] = 'Alta probabilidade de texto gerado por modelo de linguagem (LLM)';
            $explanation[] = 'Padrão sintático muito uniforme e previsível';
            $explanation[] = 'Baixa variação de vocabulário para o contexto';
        } elseif ($aiScore >= 0.4) {
            $explanation[] = 'Padrões mistos — texto pode ter sido parcialmente gerado ou editado por IA';
            $explanation[] = 'Características ambíguas entre escrita humana e gerada por modelo';
        } else {
            $explanation[] = 'Padrões linguísticos consistentes com escrita humana';
            $explanation[] = 'Variação natural de vocabulário e estrutura de frase detectada';
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
