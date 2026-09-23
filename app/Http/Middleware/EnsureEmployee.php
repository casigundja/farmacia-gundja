<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isActiveEmployee = $user?->isEmployee() && $user->status && ($user->employee?->active ?? false);
        abort_unless($user?->isAdmin() && $user->status || $isActiveEmployee, 403);

        return $next($request);
    }
}
