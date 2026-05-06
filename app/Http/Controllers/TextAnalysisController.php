<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeTextRequest;
use App\Models\TextAnalysis;
use App\Services\TextDetectionService;
use Inertia\Inertia;
use Inertia\Response;

class TextAnalysisController extends Controller
{
    public function __construct(private TextDetectionService $service) {}

    public function store(AnalyzeTextRequest $request)
    {
        $validated = $request->validated();
        $content   = $validated['content'];
        $model     = $validated['model'];

        try {
            $result = $this->service->analyzeText($content, $model);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['content' => $e->getMessage()])->withInput();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['content' => $e->getMessage()])->withInput();
        }

        $analysis = $request->user()->textAnalyses()->create([
            'content'        => $content,
            'ai_score'       => $result['ai_score'],
            'classification' => $result['classification'],
            'explanation'    => $result['explanation'],
            'model'          => $model,
        ]);

        return redirect()->route('text-analyses.show', $analysis);
    }

    public function show(TextAnalysis $textAnalysis): Response
    {
        abort_if($textAnalysis->user_id !== auth()->id(), 403);

        return Inertia::render('Text/Result', [
            'analysis' => $textAnalysis,
        ]);
    }

    public function index(): Response
    {
        $analyses = auth()->user()->textAnalyses()
            ->latest('created_at')
            ->paginate(15);

        return Inertia::render('Text/History', [
            'analyses' => $analyses,
        ]);
    }
}
