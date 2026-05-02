<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AudioAnalysisController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TextAnalysisController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Landing');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Index'))->name('dashboard');

    Route::get('/image', fn () => Inertia::render('Dashboard'))->name('image.upload');
    Route::post('/analyze', [AnalysisController::class, 'store'])->name('image-analyses.store');
    Route::get('/image-analyses', [AnalysisController::class, 'index'])->name('image-analyses.index');
    Route::get('/image-analyses/{analysis}', [AnalysisController::class, 'show'])->name('image-analyses.show');

    Route::get('/text',fn () => Inertia::render('Text/Upload'))->name('text.upload');
    Route::post('/analyze/text', [TextAnalysisController::class, 'store'])->name('text-analyses.store');
    Route::get('/text-analyses', [TextAnalysisController::class, 'index'])->name('text-analyses.index');
    Route::get('/text-analyses/{textAnalysis}', [TextAnalysisController::class, 'show'])->name('text-analyses.show');

    Route::get('/audio', fn () => Inertia::render('Audio/Upload'))->name('audio.upload');
    Route::post('/analyze/audio', [AudioAnalysisController::class, 'store'])->name('audio-analyses.store');
    Route::get('/audio-analyses', [AudioAnalysisController::class, 'index'])->name('audio-analyses.index');
    Route::get('/audio-analyses/{audioAnalysis}', [AudioAnalysisController::class, 'show'])->name('audio-analyses.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
