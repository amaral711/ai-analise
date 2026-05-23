<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserIp;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): \Illuminate\Http\RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            return redirect()->route('login')->withErrors(['email' => 'Falha ao autenticar com o Google.']);
        }

        $user = User::firstOrNew(
            ['email' => $googleUser->getEmail()]
        );

        $isNew = !$user->exists;

        if ($isNew && UserIp::where('ip', request()->ip())->exists()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Já existe uma conta associada a este endereço de rede.',
            ]);
        }

        $user->fill([
            'name'              => $googleUser->getName(),
            'google_id'         => $googleUser->getId(),
            'avatar'            => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        if ($isNew) {
            event(new Registered($user));
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
