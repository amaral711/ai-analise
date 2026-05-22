<?php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\Payment;
use App\Services\MercadoPagoService;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(private MercadoPagoService $mercadoPago) {}

    public function index(): Response
    {
        $packages = CreditPackage::active()->orderBy('credits')->get();

        return Inertia::render('Credits/Index', [
            'packages' => $packages,
        ]);
    }

    public function checkout(CreditPackage $package)
    {
        abort_if(! $package->is_active, 404);

        $user = auth()->user();

        $result = $this->mercadoPago->createPixPayment($user, $package);

        $payment = Payment::create([
            'user_id'            => $user->id,
            'credit_package_id'  => $package->id,
            'payment_id'         => $result['payment_id'],
            'status'             => 'pending',
            'amount'             => $package->price,
            'credits'            => $package->credits,
            'pix_qr_code'        => $result['pix_qr_code'],
            'pix_qr_code_base64' => $result['pix_qr_code_base64'],
            'expires_at'         => $result['expires_at'],
        ]);

        return Inertia::render('Credits/Checkout', [
            'payment' => [
                'id'                 => $payment->id,
                'pix_qr_code'        => $payment->pix_qr_code,
                'pix_qr_code_base64' => $payment->pix_qr_code_base64,
                'expires_at'         => $payment->expires_at,
                'credits'            => $payment->credits,
                'amount'             => $payment->amount,
            ],
            'package' => $package,
        ]);
    }

    public function history(): Response
    {
        $transactions = auth()->user()
            ->creditTransactions()
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('Credits/History', [
            'transactions' => $transactions,
        ]);
    }
}
