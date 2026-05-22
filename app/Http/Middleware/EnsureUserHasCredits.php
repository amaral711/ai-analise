<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasCredits
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->credits < 1) {
            return redirect()->route('credits.index')
                ->with('error', 'Você não tem créditos suficientes. Compre mais para continuar.');
        }

        return $next($request);
    }
}
