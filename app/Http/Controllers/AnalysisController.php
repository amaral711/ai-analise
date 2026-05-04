<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeImageRequest;
use App\Models\Analysis;
use App\Services\AiDetectionService;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisController extends Controller
{
    public function __construct(private AiDetectionService $service) {}

    public function store(AnalyzeImageRequest $request)
    {
        $file = $request->file('image');

        try {
            $result = $this->service->analyzeImage($file);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['image' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['image' => $e->getMessage()]);
        }

        $ext      = $file->getClientOriginalExtension();
        $filename = uniqid() . '.' . $ext;

        $imagePath = Storage::disk('s3')->putFileAs('', $file, $filename, 'public');

        $analysis = $request->user()->analyses()->create([
            'text'           => $file->getClientOriginalName(),
            'image_path'     => $imagePath,
            'ai_score'       => $result['ai_score'],
            'classification' => $result['classification'],
            'explanation'    => $result['explanation'],
        ]);

        return redirect()->route('image-analyses.show', $analysis);
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
