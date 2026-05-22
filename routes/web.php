<?php

use App\Http\Controllers\Admin\CreditGrantController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AudioAnalysisController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TextAnalysisController;
use App\Http\Controllers\WaitingController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Landing');
})->name('home');

// Webhook público (sem CSRF, sem auth)
Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadopago'])
    ->name('webhooks.mercadopago');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => Inertia::render('Index'))->name('dashboard');

    Route::get('/waiting', [WaitingController::class, 'index'])->name('analyses.waiting');
    Route::get('/analyses/pending', [WaitingController::class, 'pending'])->name('analyses.pending');

    // Análise de imagem
    Route::get('/image', fn () => Inertia::render('Dashboard'))->name('image.upload');
    Route::post('/analyze', [AnalysisController::class, 'store'])->middleware('credits')->name('image-analyses.store');
    Route::get('/image-analyses', [AnalysisController::class, 'index'])->name('image-analyses.index');
    Route::get('/image-analyses/{analysis}', [AnalysisController::class, 'show'])->name('image-analyses.show');

    // Análise de texto
    Route::get('/text', fn () => Inertia::render('Text/Upload'))->name('text.upload');
    Route::post('/analyze/text', [TextAnalysisController::class, 'store'])->middleware('credits')->name('text-analyses.store');
    Route::get('/text-analyses', [TextAnalysisController::class, 'index'])->name('text-analyses.index');
    Route::get('/text-analyses/{textAnalysis}', [TextAnalysisController::class, 'show'])->name('text-analyses.show');

    // Análise de áudio
    Route::get('/audio', fn () => Inertia::render('Audio/Upload'))->name('audio.upload');
    Route::post('/analyze/audio', [AudioAnalysisController::class, 'store'])->middleware('credits')->name('audio-analyses.store');
    Route::get('/audio-analyses', [AudioAnalysisController::class, 'index'])->name('audio-analyses.index');
    Route::get('/audio-analyses/{audioAnalysis}', [AudioAnalysisController::class, 'show'])->name('audio-analyses.show');

    // Créditos
    Route::get('/credits', [PaymentController::class, 'index'])->name('credits.index');
    Route::post('/credits/{package}/checkout', [PaymentController::class, 'checkout'])->name('credits.checkout');
    Route::get('/credits/history', [PaymentController::class, 'history'])->name('credits.history');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}/credits', [CreditGrantController::class, 'show'])->name('users.credits');
        Route::post('/users/{user}/credits', [CreditGrantController::class, 'store'])->name('users.credits.store');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    });
});

require __DIR__.'/auth.php';
