<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreditGrantController extends Controller
{
    public function __construct(private CreditService $creditService) {}

    public function show(User $user): Response
    {
        $transactions = $user->creditTransactions()
            ->latest('created_at')
            ->limit(20)
            ->get();

        return Inertia::render('Admin/Users/Credits', [
            'targetUser'   => $user->only('id', 'name', 'email', 'credits'),
            'transactions' => $transactions,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'credits' => 'required|integer|not_in:0|between:-1000,1000',
            'reason'  => 'required|string|max:255',
        ]);

        $this->creditService->grantManually(
            $user,
            $validated['credits'],
            $validated['reason'],
            $request->user(),
        );

        return back()->with('success', 'Créditos atualizados com sucesso.');
    }
}
