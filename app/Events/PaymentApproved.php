<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->payment->user_id);
    }

    public function broadcastAs(): string
    {
        return 'payment.approved';
    }

    public function broadcastWith(): array
    {
        return [
            'payment_id'     => $this->payment->id,
            'credits_added'  => $this->payment->credits,
            'credits_balance' => $this->payment->user->credits,
        ];
    }
}
