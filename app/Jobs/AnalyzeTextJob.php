<?php

namespace App\Jobs;

use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Models\TextAnalysis;
use App\Services\TextDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AnalyzeTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;
    public bool $deleteWhenMissingModels = true;

    public function __construct(public TextAnalysis $analysis) {}

    public function handle(TextDetectionService $service): void
    {
        $this->analysis->update(['status' => 'processing']);

        try {
            $result = $service->analyzeText($this->analysis->content, $this->analysis->model);

            $this->analysis->update([
                'ai_score'       => $result['ai_score'],
                'classification' => $result['classification'],
                'explanation'    => $result['explanation'],
                'status'         => 'completed',
            ]);

            AnalysisCompleted::dispatch($this->analysis, 'text');
        } catch (\Throwable $e) {
            $this->analysis->update(['status' => 'failed']);
            AnalysisFailed::dispatch($this->analysis, 'text');
            throw $e;
        }
    }
}
