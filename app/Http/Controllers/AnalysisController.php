<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeRequest;
use App\Models\Analysis;
use App\Services\AiDetectionService;
use Inertia\Inertia;
use Inertia\Response;

class AnalysisController extends Controller
{
    public function __construct(private AiDetectionService $service) {}

    public function store(AnalyzeRequest $request)
    {
        $result = $this->service->analyze($request->text);

        $analysis = $request->user()->analyses()->create([
            'text'           => $request->text,
            'ai_score'       => $result['ai_score'],
            'classification' => $result['classification'],
            'explanation'    => $result['explanation'],
        ]);

        return redirect()->route('analyses.show', $analysis);
    }

    public function show(Analysis $analysis): Response
    {
        abort_if($analysis->user_id !== auth()->id(), 403);

        return Inertia::render('Analysis/Result', [
            'analysis' => $analysis,
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
