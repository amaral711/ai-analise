<?php

namespace App\Services;

use App\Models\CreditPackage;
use App\Models\User;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function createPixPayment(User $user, CreditPackage $package): array
    {
        $client = new PaymentClient();

        $payload = [
            'transaction_amount' => (float) $package->price,
            'description'        => "DeepScan - {$package->credits} creditos ({$package->name})",
            'payment_method_id'  => 'pix',
            'payer'              => [
                'email' => $user->email,
            ],
            'date_of_expiration' => now()->addMinutes(30)->format('Y-m-d\TH:i:s.000P'),
        ];

        if (app()->isProduction()) {
            $payload['notification_url'] = route('webhooks.mercadopago');
        }

        try {
            $payment = $client->create($payload);
        } catch (MPApiException $e) {
            $body = $e->getApiResponse()?->getContent();
            throw new \RuntimeException('Mercado Pago API error: ' . json_encode($body), 0, $e);
        }

        return [
            'payment_id'         => (string) $payment->id,
            'status'             => $payment->status,
            'pix_qr_code'        => $payment->point_of_interaction->transaction_data->qr_code ?? null,
            'pix_qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
            'expires_at'         => now()->addMinutes(30),
        ];
    }

    public function getPayment(string $paymentId): array
    {
        $client = new PaymentClient();
        $payment = $client->get((int) $paymentId);

        return [
            'id'     => (string) $payment->id,
            'status' => $payment->status,
        ];
    }

    public function validateWebhookSignature(Request $request): bool
    {
        $secret = config('services.mercadopago.webhook_secret');

        if (! $secret) {
            return true;
        }

        $signatureHeader = $request->header('x-signature', '');
        $requestId       = $request->header('x-request-id', '');

        $parts = [];
        foreach (explode(';', $signatureHeader) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[trim($key)] = trim($value);
        }

        $ts = $parts['ts'] ?? '';
        $v1 = $parts['v1'] ?? '';

        if (! $ts || ! $v1) {
            return false;
        }

        $dataId  = $request->input('data.id', '');
        $message = "id:{$dataId};request-id:{$requestId};ts:{$ts}";
        $hash    = hash_hmac('sha256', $message, $secret);

        return hash_equals($hash, $v1);
    }
}
