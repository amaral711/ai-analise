<?php

namespace App\Services;

use App\Events\PaymentApproved;
use App\Models\CreditTransaction;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function hasCredits(User $user, int $amount = 1): bool
    {
        return $user->credits >= $amount;
    }

    public function grantFreemium(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->increment('credits', 10);
            $user->refresh();

            CreditTransaction::create([
                'user_id'      => $user->id,
                'type'         => 'freemium',
                'credits_delta' => 10,
                'balance_after' => $user->credits,
                'description'  => 'Bônus de boas-vindas',
            ]);
        });
    }

    public function deductForAnalysis(User $user, string $analysisType, int $referenceId, int $amount = 1): void
    {
        DB::transaction(function () use ($user, $analysisType, $referenceId, $amount) {
            $user->decrement('credits', $amount);
            $user->refresh();

            $labels = [
                'image' => 'Análise de imagem',
                'text'  => 'Análise de texto',
                'audio' => 'Análise de áudio',
            ];

            CreditTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'analysis_consumed',
                'credits_delta'  => -$amount,
                'balance_after'  => $user->credits,
                'description'    => $labels[$analysisType] ?? 'Análise realizada',
                'reference_type' => $analysisType,
                'reference_id'   => $referenceId,
            ]);
        });
    }

    public function grantManually(User $user, int $credits, string $reason, User $admin): void
    {
        $type = $credits > 0 ? 'manual_grant' : 'manual_deduct';

        DB::transaction(function () use ($user, $credits, $reason, $admin, $type) {
            $user->increment('credits', $credits);
            $user->refresh();

            CreditTransaction::create([
                'user_id'        => $user->id,
                'type'           => $type,
                'credits_delta'  => $credits,
                'balance_after'  => $user->credits,
                'description'    => $reason . ' (por ' . $admin->name . ')',
                'reference_type' => 'admin',
                'reference_id'   => $admin->id,
            ]);
        });
    }

    public function addFromPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'approved']);

            $user = $payment->user;
            $user->increment('credits', $payment->credits);
            $user->refresh();

            CreditTransaction::create([
                'user_id'        => $user->id,
                'type'           => 'purchase',
                'credits_delta'  => $payment->credits,
                'balance_after'  => $user->credits,
                'description'    => 'Compra de créditos via PIX',
                'reference_type' => 'payment',
                'reference_id'   => $payment->id,
            ]);
        });

        $payment->refresh();
        PaymentApproved::dispatch($payment);
    }
}
