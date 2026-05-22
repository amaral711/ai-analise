<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_users'            => User::count(),
            'total_revenue'          => Payment::where('status', 'approved')->sum('amount'),
            'total_approved_payments' => Payment::where('status', 'approved')->count(),
            'total_pending_payments'  => Payment::where('status', 'pending')->count(),
            'credits_in_circulation' => User::sum('credits'),
        ];

        $recentPayments = Payment::with(['user', 'creditPackage'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id'         => $p->id,
                'user'       => ['name' => $p->user->name, 'email' => $p->user->email],
                'package'    => $p->creditPackage?->name,
                'credits'    => $p->credits,
                'amount'     => $p->amount,
                'status'     => $p->status,
                'created_at' => $p->created_at,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats'          => $stats,
            'recentPayments' => $recentPayments,
        ]);
    }
}
