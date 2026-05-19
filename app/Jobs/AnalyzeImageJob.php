<?php

namespace App\Jobs;

use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Models\Analysis;
use App\Services\AiDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AnalyzeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        public Analysis $analysis,
        public string $tempPath,
        public string $originalName,
    ) {}

    public function handle(AiDetectionService $service): void
    {
        $this->analysis->update(['status' => 'processing']);

        $tmpPath = null;

        try {
            $contents = Storage::disk('s3')->get($this->tempPath);
            $tmpPath  = tempnam(sys_get_temp_dir(), 'anlz_');
            file_put_contents($tmpPath, $contents);

            $result = $service->analyzeFromPath($tmpPath, $this->originalName);

            $this->analysis->update([
                'ai_score'       => $result['ai_score'],
                'classification' => $result['classification'],
                'explanation'    => $result['explanation'],
                'status'         => 'completed',
            ]);

            AnalysisCompleted::dispatch($this->analysis, 'image');
        } catch (\Throwable $e) {
            $this->analysis->update(['status' => 'failed']);
            AnalysisFailed::dispatch($this->analysis, 'image');
            throw $e;
        } finally {
            if ($tmpPath && file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }
    }
}
