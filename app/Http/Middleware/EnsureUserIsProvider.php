<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->provider === null) {
            return redirect()->route('claim.index')->with('status', 'Claim or add your business to open the painter dashboard.');
        }

        return $next($request);
    }
}
