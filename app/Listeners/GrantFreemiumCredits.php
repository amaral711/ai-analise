<?php

namespace App\Listeners;

use App\Services\CreditService;
use Illuminate\Auth\Events\Registered;

class GrantFreemiumCredits
{
    public function __construct(private CreditService $creditService) {}

    public function handle(Registered $event): void
    {
        $this->creditService->grantFreemium($event->user);
    }
}
