<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $payments = Payment::with(['user', 'creditPackage'])
            ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
            ->when(request('search'), fn ($q, $s) =>
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($p) => [
                'id'         => $p->id,
                'user'       => ['name' => $p->user->name, 'email' => $p->user->email],
                'package'    => $p->creditPackage?->name,
                'credits'    => $p->credits,
                'amount'     => $p->amount,
                'status'     => $p->status,
                'payment_id' => $p->payment_id,
                'created_at' => $p->created_at,
            ]);

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'filters'  => request()->only('status', 'search'),
        ]);
    }
}
