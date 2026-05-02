<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeAudioRequest;
use App\Models\AudioAnalysis;
use App\Services\AudioDetectionService;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AudioAnalysisController extends Controller
{
    public function __construct(private AudioDetectionService $service) {}

    public function store(AnalyzeAudioRequest $request)
    {
        $file = $request->file('audio');

        try {
            $result = $this->service->analyzeAudio($file);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['audio' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['audio' => $e->getMessage()]);
        }

        $ext      = $file->getClientOriginalExtension();
        $filename = 'audio/' . uniqid() . '.' . $ext;

        Storage::disk('s3')->putFileAs('', $file, $filename, 'public');

        $analysis = $request->user()->audioAnalyses()->create([
            'text'           => $file->getClientOriginalName(),
            'audio_path'     => $filename,
            'ai_score'       => $result['ai_score'],
            'classification' => $result['classification'],
            'explanation'    => $result['explanation'],
        ]);

        return redirect()->route('audio-analyses.show', $analysis);
    }

    public function show(AudioAnalysis $audioAnalysis): Response
    {
        abort_if($audioAnalysis->user_id !== auth()->id(), 403);

        return Inertia::render('Audio/Result', [
            'analysis' => $audioAnalysis,
            'audioUrl' => $audioAnalysis->audio_path
                ? Storage::disk('s3')->url($audioAnalysis->audio_path)
                : null,
        ]);
    }

    public function index(): Response
    {
        $analyses = auth()->user()->audioAnalyses()
            ->latest('created_at')
            ->paginate(15);

        return Inertia::render('Audio/History', [
            'analyses' => $analyses,
        ]);
    }
}
