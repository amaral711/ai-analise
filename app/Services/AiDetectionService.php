<?php

namespace App\Services;

class AiDetectionService
{
    private const AI_PHRASES = [
        // Inglês
        "i'd be happy", 'certainly!', 'of course!', 'as an ai',
        'great question', "it's important to note", 'in conclusion',
        'in summary', 'furthermore,', 'moreover,', 'additionally,',
        "it's worth noting", 'first and foremost', 'last but not least',
        'as mentioned earlier', 'needless to say', 'it is worth mentioning',
        'i understand that', 'rest assured', 'feel free to',

        // Português — marcadores de transição excessivos
        'além disso,', 'por outro lado,', 'em suma,', 'em conclusão,',
        'dessa forma,', 'portanto,', 'sendo assim,', 'nesse sentido,',
        'vale ressaltar', 'vale destacar', 'é importante destacar',
        'é importante ressaltar', 'é válido mencionar', 'cabe salientar',
        'é fundamental', 'é essencial', 'é primordial',

        // Português — abre/encerra resposta de IA
        'claro!', 'com certeza!', 'com prazer!', 'ótima pergunta',
        'fico feliz em ajudar', 'espero ter ajudado', 'espero ter esclarecido',
        'qualquer dúvida', 'sinta-se à vontade', 'não hesite em',
        'como mencionado anteriormente', 'como dito anteriormente',
        'em termos gerais,', 'de forma geral,', 'de maneira geral,',

        // Português — estrutura de lista artificial
        'em primeiro lugar,', 'em segundo lugar,', 'em terceiro lugar,',
        'por fim,', 'por último,', 'primeiramente,', 'inicialmente,',
        'ademais,', 'outrossim,', 'todavia,', 'contudo,',
    ];

    public function analyze(string $text): array
    {
        $scores = [
            'vocab'     => $this->scoreVocabularyDiversity($text),
            'sentences' => $this->scoreSentenceLengthVariance($text),
            'phrases'   => $this->scoreAiPhrases($text),
            'structure' => $this->scoreStructuralUniformity($text),
            'formality' => $this->scoreFormality($text),
        ];

        $weights = [
            'vocab'     => 0.25,
            'sentences' => 0.25,
            'phrases'   => 0.20,
            'structure' => 0.15,
            'formality' => 0.15,
        ];

        $aiScore = 0.0;
        foreach ($scores as $key => $score) {
            $aiScore += $score * $weights[$key];
        }
        $aiScore = round(min(1.0, max(0.0, $aiScore)), 2);

        $classification = match (true) {
            $aiScore < 0.4 => 'human',
            $aiScore < 0.7 => 'inconclusive',
            default        => 'ai',
        };

        return [
            'ai_score'       => $aiScore,
            'classification' => $classification,
            'explanation'    => $this->generateExplanations($scores),
        ];
    }

    private function scoreVocabularyDiversity(string $text): float
    {
        $words = $this->extractWords($text);

        if (count($words) < 20) {
            return 0.5;
        }

        $ttr = count(array_unique($words)) / count($words);

        // TTR baixo → vocabulário repetitivo → mais IA
        return (float) max(0, min(1, 1.2 - ($ttr * 1.5)));
    }

    private function scoreSentenceLengthVariance(string $text): float
    {
        $sentences = array_values(array_filter(
            preg_split('/(?<=[.!?])\s+/u', $text),
            fn($s) => count($this->extractWords($s)) >= 3
        ));

        if (count($sentences) < 3) {
            return 0.5;
        }

        $lengths  = array_map(fn($s) => count($this->extractWords($s)), $sentences);
        $mean     = array_sum($lengths) / count($lengths);
        $variance = array_sum(array_map(fn($l) => ($l - $mean) ** 2, $lengths)) / count($lengths);
        $stdDev   = sqrt($variance);

        // Desvio padrão baixo → frases uniformes → mais IA
        return (float) max(0, min(1, 1 - ($stdDev / 10)));
    }

    private function scoreAiPhrases(string $text): float
    {
        $lower = strtolower($text);
        $found = 0;

        foreach (self::AI_PHRASES as $phrase) {
            if (str_contains($lower, $phrase)) {
                $found++;
            }
        }

        return (float) min(1.0, $found * 0.3);
    }

    private function scoreStructuralUniformity(string $text): float
    {
        $lines = explode("\n", trim($text));
        $total = count($lines);

        if ($total < 3) {
            return 0.2;
        }

        $structured = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d+[.)]\s/', $line)) $structured++;
            elseif (preg_match('/^[-•*]\s/', $line)) $structured++;
            elseif (preg_match('/^[A-Z][^.!?]{0,30}:\s*$/', $line)) $structured++;
        }

        return (float) min(1.0, ($structured / $total) * 2);
    }

    private function scoreFormality(string $text): float
    {
        $words = $this->extractWords($text);

        if (count($words) < 15) {
            return 0.4;
        }

        $avgLen = array_sum(array_map('strlen', $words)) / count($words);

        // Palavras mais longas em média → mais formal → padrão de IA
        return (float) max(0, min(1, ($avgLen - 3) / 6));
    }

    private function generateExplanations(array $scores): array
    {
        $explanations = [];

        if ($scores['vocab'] > 0.65) {
            $explanations[] = 'Baixa variação de vocabulário';
        } elseif ($scores['vocab'] < 0.30) {
            $explanations[] = 'Alta diversidade de vocabulário';
        }

        if ($scores['sentences'] > 0.65) {
            $explanations[] = 'Comprimento das frases muito uniforme';
        } elseif ($scores['sentences'] < 0.30) {
            $explanations[] = 'Variação natural no tamanho das frases';
        }

        if ($scores['phrases'] > 0.25) {
            $explanations[] = 'Uso de expressões típicas de texto gerado por IA';
        }

        if ($scores['structure'] > 0.50) {
            $explanations[] = 'Estrutura excessivamente organizada e formatada';
        }

        if ($scores['formality'] > 0.65) {
            $explanations[] = 'Tom excessivamente formal e técnico';
        } elseif ($scores['formality'] < 0.30) {
            $explanations[] = 'Linguagem informal e coloquial';
        }

        if (empty($explanations)) {
            $explanations[] = 'Nenhum padrão dominante identificado';
        }

        return $explanations;
    }

    private function extractWords(string $text): array
    {
        preg_match_all('/\b[a-zA-Zà-úÀ-Ú]+\b/u', strtolower($text), $matches);
        return $matches[0];
    }
}
