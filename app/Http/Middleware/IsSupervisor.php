<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsSupervisor
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is a supervisor
        $user = $request->user();

        if (!$user->is_supervisor) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
