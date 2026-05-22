<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyzeTextRequest;
use App\Jobs\AnalyzeTextJob;
use App\Models\TextAnalysis;
use App\Services\CreditService;
use Inertia\Inertia;
use Inertia\Response;


class TextAnalysisController extends Controller
{
    public function __construct(private CreditService $creditService) {}

    public function store(AnalyzeTextRequest $request)
    {
        $validated = $request->validated();

        $analysis = $request->user()->textAnalyses()->create([
            'content' => $validated['content'],
            'model'   => $validated['model'],
            'status'  => 'pending',
        ]);

        AnalyzeTextJob::dispatch($analysis);

        $this->creditService->deductForAnalysis($request->user(), 'text', $analysis->id);

        return redirect()->route('analyses.waiting');
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
