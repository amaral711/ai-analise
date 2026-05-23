<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TextDetectionService
{
    public function analyzeText(string $text, string $model = 'detecting_ai'): array
    {
        if ($model === 'claude') {
            return $this->analyzeWithClaude($text);
        }

        $isProduction = app()->environment('production');

        [$url, $token, $body] = $isProduction
            ? $this->productionConfig($model, $text)
            : $this->localConfig($model, $text);

        Log::info('Text AI analysis start', ['url' => $url, 'model' => $model, 'length' => strlen($text)]);

        // HF Spaces gratuitos dormem após inatividade — cold start pode levar 90s
        $attempts = [60, 90];

        foreach ($attempts as $i => $timeout) {
            try {
                $request = Http::timeout($timeout)->acceptJson();

                if ($token) {
                    $request = $request->withToken($token);
                }

                $response = $request->post($url, $body);

                Log::info('Text AI response', ['status' => $response->status(), 'attempt' => $i + 1]);

                if ($response->successful()) {
                    return $this->buildResult($response->json(), $model);
                }

                if ($response->status() === 400 || $response->status() === 422) {
                    throw new \InvalidArgumentException($response->json('detail') ?? 'Erro ao analisar texto.');
                }

                Log::warning('Text AI service error', ['status' => $response->status()]);
                break;
            } catch (\InvalidArgumentException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::warning('Text AI attempt failed', ['attempt' => $i + 1, 'error' => $e->getMessage()]);

                if ($i === array_key_last($attempts)) {
                    break;
                }
            }
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

    private function analyzeWithClaude(string $text): array
    {
        $apiKey = config('services.anthropic.key');
        $model  = config('services.anthropic.model', 'claude-haiku-4-5-20251001');

        $systemPrompt = 'You are an expert AI-generated text detector. Analyze the submitted text and determine if it was written by a human or generated by an AI model (LLM).

Evaluate these characteristics:
- Syntactic uniformity and structural repetition
- Vocabulary diversity and naturalness
- Emotional authenticity and subjectivity
- LLM-typical patterns (excessive formality, hedging language, formulaic structure, overuse of transitions)
- Natural vs. artificial errors
- Coherence and cohesion patterns

Return ONLY valid JSON, no markdown, no extra text:
{"ai_score": <float 0.0-1.0>, "reasoning": ["observation 1", "observation 2", "observation 3"]}

Rules:
- ai_score: 0.0 = definitely human, 1.0 = definitely AI-generated
- reasoning: 3-5 specific, concrete observations about the text patterns
- CRITICAL: Write ALL reasoning observations in the SAME language as the submitted text. If the text is in Portuguese, write in Portuguese. If in English, write in English. Never mix languages.';

        $cacheKey = 'text_analysis:claude:v2:' . hash('sha256', $text);

        if ($cached = Cache::get($cacheKey)) {
            Log::info('Claude AI analysis cache hit', ['length' => strlen($text)]);
            return $cached;
        }

        Log::info('Claude AI analysis start', ['model' => $model, 'length' => strlen($text)]);

        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'anthropic-beta'    => 'prompt-caching-2024-07-31',
                'content-type'      => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model'      => $model,
                'max_tokens' => 512,
                'system'     => [
                    [
                        'type'          => 'text',
                        'text'          => $systemPrompt,
                        'cache_control' => ['type' => 'ephemeral'],
                    ],
                ],
                'messages' => [
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

        Log::info('Claude AI response', ['status' => $response->status()]);

        if (!$response->successful()) {
            Log::error('Claude API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Serviço de análise indisponível. Tente novamente.');
        }

        $content = $response->json('content.0.text') ?? '';
        $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
        $content = preg_replace('/\s*```$/', '', $content);
        $parsed  = json_decode(trim($content), true);

        if (!$parsed || !isset($parsed['ai_score'])) {
            Log::error('Claude API unexpected response', ['content' => $content]);
            throw new \RuntimeException('Serviço de análise retornou resposta inválida.');
        }

        $aiScore = round((float) $parsed['ai_score'], 2);
        $aiScore = max(0.0, min(1.0, $aiScore));

        $classification = match (true) {
            $aiScore < 0.4 => 'human',
            $aiScore < 0.7 => 'inconclusive',
            default        => 'ai',
        };

        $explanation   = array_values((array) ($parsed['reasoning'] ?? []));
        $explanation[] = 'Modelo utilizado: Claude Haiku (análise semântica avançada)';

        $result = [
            'ai_score'       => $aiScore,
            'classification' => $classification,
            'explanation'    => $explanation,
        ];

        Cache::put($cacheKey, $result, now()->addDays(7));

        return $result;
    }
}
