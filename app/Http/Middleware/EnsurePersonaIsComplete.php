<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonaIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Push signed-in users without a completed persona into onboarding first.
        if ($user instanceof User
            && $user->persona?->completed_at === null
            && ! $request->routeIs('onboarding.*')) {
            return redirect()->route('onboarding.edit');
        }

        return $next($request);
    }
}
