<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeAudioRequest;
use App\Jobs\AnalyzeAudioJob;
use App\Models\AudioAnalysis;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AudioAnalysisController extends Controller
{
    public function store(AnalyzeAudioRequest $request)
    {
        $file  = $request->file('audio');
        $ext   = $file->getClientOriginalExtension();
        $s3Key = 'audio/' . uniqid() . '.' . $ext;

        Storage::disk('s3')->put($s3Key, file_get_contents($file->path()), 'public');

        $analysis = $request->user()->audioAnalyses()->create([
            'text'       => $file->getClientOriginalName(),
            'audio_path' => $s3Key,
            'status'     => 'pending',
        ]);

        AnalyzeAudioJob::dispatch($analysis, $s3Key, $file->getClientOriginalName());

        return redirect()->route('analyses.waiting');
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
