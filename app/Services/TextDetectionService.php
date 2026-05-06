<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextDetectionService
{
    public function analyzeText(string $text, string $model = 'detecting_ai'): array
    {
        $isProduction = app()->environment('production');

        [$url, $token, $body] = $isProduction
            ? $this->productionConfig($model, $text)
            : $this->localConfig($model, $text);

        Log::info('Text AI analysis start', ['url' => $url, 'model' => $model, 'length' => strlen($text)]);

        try {
            $request = Http::timeout(60)->acceptJson();

            if ($token) {
                $request = $request->withToken($token);
            }

            $response = $request->post($url, $body);

            Log::info('Text AI response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                return $this->buildResult($response->json(), $model);
            }

            if ($response->status() === 400 || $response->status() === 422) {
                throw new \InvalidArgumentException($response->json('detail') ?? 'Erro ao analisar texto.');
            }

            Log::warning('Text AI service error', ['status' => $response->status()]);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::warning('Text AI service unavailable', ['error' => $e->getMessage()]);
        }

        throw new \RuntimeException('Serviço de análise indisponível. Tente novamente.');
    }

    private function productionConfig(string $model, string $text): array
    {
        $key   = $model === 'bert' ? 'text_bert' : 'text_detecting_ai';
        $url   = config("services.{$key}.url");
        $token = config("services.{$key}.token");

        return [$url, $token, ['text' => $text]];
    }

    private function localConfig(string $model, string $text): array
    {
        $url = rtrim(config('services.python_ai.url', 'http://python-ai:8001'), '/') . '/detect/text';

        return [$url, null, ['text' => $text, 'model' => $model]];
    }

    private function buildResult(array $data, string $model): array
    {
        $aiScore   = round((float) ($data['ai_score'] ?? 0.5), 2);
        $confianca = $data['confianca'] ?? 'media';

        $classification = match (true) {
            $aiScore < 0.4 => 'human',
            $aiScore < 0.7 => 'inconclusive',
            default        => 'ai',
        };

        $explanation = $model === 'bert'
            ? $this->bertExplanation($data, $aiScore)
            : $this->detectingAiExplanation($aiScore);

        $confidenceLabel = match ($confianca) {
            'alta'  => 'alta',
            'baixa' => 'baixa',
            default => 'média',
        };

        $explanation[] = "Confiança da análise: {$confidenceLabel}";
        $explanation[] = 'Modelo utilizado: ' . ($data['model'] ?? $model);

        return [
            'ai_score'       => $aiScore,
            'classification' => $classification,
            'explanation'    => $explanation,
        ];
    }

    private function bertExplanation(array $data, float $aiScore): array
    {
        if ($data['warning'] ?? null) {
            return [
                'Aviso: ' . $data['warning'],
                'Texto muito curto para análise precisa — forneça ao menos 3 frases completas.',
            ];
        }

        $avgPerp    = isset($data['avg_perplexity']) ? round($data['avg_perplexity'], 1) : '?';
        $burstiness = isset($data['burstiness']) ? round($data['burstiness'], 1) : '?';

        $lines = [
            "Perplexidade média das sentenças: {$avgPerp}",
            "Variação entre sentenças (burstiness): {$burstiness}",
        ];

        if ($aiScore >= 0.7) {
            $lines[] = 'Alta uniformidade sintática detectada — padrão típico de geração por IA';
            $lines[] = 'Baixa perplexidade indica texto muito previsível para o modelo de linguagem';
        } elseif ($aiScore >= 0.4) {
            $lines[] = 'Padrões mistos — texto pode ter sido parcialmente gerado ou editado por IA';
            $lines[] = 'Perplexidade e variação ambíguas entre escrita humana e gerada por modelo';
        } else {
            $lines[] = 'Alta variação entre sentenças — padrão consistente com escrita humana';
            $lines[] = 'Perplexidade elevada indica texto menos previsível e mais natural';
        }

        return $lines;
    }

    private function detectingAiExplanation(float $aiScore): array
    {
        if ($aiScore >= 0.7) {
            return [
                'Alta probabilidade de texto gerado por modelo de linguagem (LLM)',
                'Padrão sintático muito uniforme e previsível',
                'Baixa variação de vocabulário para o contexto',
            ];
        }

        if ($aiScore >= 0.4) {
            return [
                'Padrões mistos — texto pode ter sido parcialmente gerado ou editado por IA',
                'Características ambíguas entre escrita humana e gerada por modelo',
            ];
        }

        return [
            'Padrões linguísticos consistentes com escrita humana',
            'Variação natural de vocabulário e estrutura de frase detectada',
        ];
    }
}
