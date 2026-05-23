<?php

namespace App\Listeners;

use App\Models\UserIp;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class RecordUserIp
{
    public function __construct(private Request $request) {}

    public function handle(Login $event): void
    {
        $ip = $this->request->ip();

        if (!$ip) {
            return;
        }

        UserIp::firstOrCreate([
            'user_id' => $event->user->id,
            'ip'      => $ip,
        ]);
    }
}
