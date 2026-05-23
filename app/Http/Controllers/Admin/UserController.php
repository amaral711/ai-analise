<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function show(User $user): Response
    {
        $analyses = $user->textAnalyses()
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('Admin/Users/Show', [
            'user'     => $user->only('id', 'name', 'email', 'credits', 'created_at'),
            'analyses' => $analyses,
        ]);
    }

    public function index(): Response
    {
        $users = User::query()
            ->when(request('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->withCount(['analyses', 'textAnalyses', 'audioAnalyses'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => request()->only('search'),
        ]);
    }
}
