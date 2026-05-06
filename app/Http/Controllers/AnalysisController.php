<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeImageRequest;
use App\Jobs\AnalyzeImageJob;
use App\Models\Analysis;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisController extends Controller
{
    public function store(AnalyzeImageRequest $request)
    {
        $file = $request->file('image');

        $ext      = $file->getClientOriginalExtension();
        $tempPath = 'pending/' . uniqid() . '.' . $ext;
        Storage::disk('local')->put($tempPath, file_get_contents($file->path()));

        $analysis = $request->user()->analyses()->create([
            'text'   => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        AnalyzeImageJob::dispatch($analysis, $tempPath, $file->getClientOriginalName());

        return redirect()->route('analyses.waiting');
    }

    public function show(Analysis $analysis): Response
    {
        abort_if($analysis->user_id !== auth()->id(), 403);

        return Inertia::render('Analysis/Result', [
            'analysis' => $analysis,
            'imageUrl' => $analysis->image_path ? Storage::disk('s3')->url($analysis->image_path) : null,
        ]);
    }

    public function index(): Response
    {
        $analyses = auth()->user()->analyses()
            ->latest('created_at')
            ->paginate(15);

        return Inertia::render('Analysis/History', [
            'analyses' => $analyses,
        ]);
    }
}
