<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\AudioAnalysis;
use App\Models\TextAnalysis;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class WaitingController extends Controller
{
    private function fetchPending(int $userId): \Illuminate\Support\Collection
    {
        $text = TextAnalysis::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'type'       => 'text',
                'status'     => $a->status,
                'label'      => mb_strimwidth($a->content, 0, 60, '...'),
                'created_at' => $a->created_at,
            ]);

        $image = Analysis::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'type'       => 'image',
                'status'     => $a->status,
                'label'      => $a->text,
                'created_at' => $a->created_at,
            ]);

        $audio = AudioAnalysis::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'type'       => 'audio',
                'status'     => $a->status,
                'label'      => $a->text,
                'created_at' => $a->created_at,
            ]);

        return $text->concat($image)->concat($audio)
            ->sortByDesc('created_at')
            ->values();
    }

    public function pending(): JsonResponse
    {
        return response()->json($this->fetchPending(auth()->id()));
    }

    public function index(): Response
    {
        return Inertia::render('Waiting', [
            'analyses' => $this->fetchPending(auth()->id()),
        ]);
    }
}
