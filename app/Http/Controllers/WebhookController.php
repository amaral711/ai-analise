<?php

namespace App\Http\Controllers;

use App\Events\PaymentApproved;
use App\Models\Payment;
use App\Services\CreditService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private MercadoPagoService $mercadoPago,
        private CreditService $creditService,
    ) {}

    public function mercadopago(Request $request): Response
    {
        Log::channel('stderr')->info('[MP Webhook] received', [
            'headers' => [
                'x-signature'  => $request->header('x-signature'),
                'x-request-id' => $request->header('x-request-id'),
            ],
            'body' => $request->all(),
        ]);

        if (! $this->mercadoPago->validateWebhookSignature($request)) {
            Log::channel('stderr')->warning('[MP Webhook] signature validation failed', [
                'x-signature'  => $request->header('x-signature'),
                'x-request-id' => $request->header('x-request-id'),
                'body'         => $request->all(),
            ]);
            return response('Unauthorized', 401);
        }

        $type = $request->input('type') ?? $request->input('action', '');

        Log::channel('stderr')->info('[MP Webhook] type', ['type' => $type]);

        if (! in_array($type, ['payment', 'payment.updated'])) {
            return response('OK', 200);
        }

        $paymentId = (string) $request->input('data.id');

        if (! $paymentId) {
            return response('OK', 200);
        }

        $result = $this->mercadoPago->getPayment($paymentId);

        if ($result['status'] !== 'approved') {
            $this->handleNonApproved($paymentId, $result['status']);
            return response('OK', 200);
        }

        $payment = Payment::where('payment_id', $paymentId)
            ->where('status', 'pending')
            ->first();

        if (! $payment) {
            return response('OK', 200);
        }

        $this->creditService->addFromPayment($payment);
        $payment->refresh();

        PaymentApproved::dispatch($payment);

        return response('OK', 200);
    }

    private function handleNonApproved(string $paymentId, string $status): void
    {
        $validStatuses = ['rejected', 'cancelled'];

        if (in_array($status, $validStatuses)) {
            Payment::where('payment_id', $paymentId)
                ->where('status', 'pending')
                ->update(['status' => $status]);
        }
    }
}
