<?php

namespace App\Jobs;

use App\Events\AnalysisCompleted;
use App\Events\AnalysisFailed;
use App\Models\AudioAnalysis;
use App\Services\AudioDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AnalyzeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(
        public AudioAnalysis $analysis,
        public string $tempPath,
        public string $originalName,
    ) {}

    public function handle(AudioDetectionService $service): void
    {
        $this->analysis->update(['status' => 'processing']);

        try {
            $localPath = Storage::disk('local')->path($this->tempPath);

            $result = $service->analyzeFromPath($localPath, $this->originalName);

            $ext       = pathinfo($this->tempPath, PATHINFO_EXTENSION);
            $s3Key     = 'audio/' . uniqid() . '.' . $ext;

            Storage::disk('s3')->put(
                $s3Key,
                Storage::disk('local')->get($this->tempPath),
                'public'
            );

            $this->analysis->update([
                'audio_path'     => $s3Key,
                'ai_score'       => $result['ai_score'],
                'classification' => $result['classification'],
                'explanation'    => $result['explanation'],
                'status'         => 'completed',
            ]);

            AnalysisCompleted::dispatch($this->analysis, 'audio');
        } catch (\Throwable $e) {
            $this->analysis->update(['status' => 'failed']);
            AnalysisFailed::dispatch($this->analysis, 'audio');
            throw $e;
        } finally {
            Storage::disk('local')->delete($this->tempPath);
        }
    }
}
