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
        $validated  = $request->validated();
        $creditCost = $validated['model'] === 'claude' ? 2 : 1;

        if (!$request->user()->hasRole('admin') && !$this->creditService->hasCredits($request->user(), $creditCost)) {
            return back()->withErrors(['credits' => 'Créditos insuficientes para este modelo.']);
        }

        $analysis = $request->user()->textAnalyses()->create([
            'content' => $validated['content'],
            'model'   => $validated['model'],
            'status'  => 'pending',
        ]);

        AnalyzeTextJob::dispatch($analysis);

        return redirect()->route('analyses.waiting');
    }

    public function show(TextAnalysis $textAnalysis): Response
    {
        abort_if($textAnalysis->user_id !== auth()->id() && !auth()->user()->hasRole('admin'), 403);

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
