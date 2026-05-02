<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AudioAnalysisController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    Route::post('/analyze', [AnalysisController::class, 'store'])->name('analyses.store');
    Route::get('/analyses', [AnalysisController::class, 'index'])->name('analyses.index');
    Route::get('/analyses/{analysis}', [AnalysisController::class, 'show'])->name('analyses.show');

    Route::get('/audio', fn () => Inertia::render('Audio/Upload'))->name('audio.upload');
    Route::post('/analyze/audio', [AudioAnalysisController::class, 'store'])->name('audio-analyses.store');
    Route::get('/audio-analyses', [AudioAnalysisController::class, 'index'])->name('audio-analyses.index');
    Route::get('/audio-analyses/{audioAnalysis}', [AudioAnalysisController::class, 'show'])->name('audio-analyses.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
