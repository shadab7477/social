<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAgencyStaff
{
    /**
     * Restrict client_viewer accounts from accessing agency-only routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isClientViewer()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Restricted to agency staff.'], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'This area is only available to agency staff.');
        }

        return $next($request);
    }
}

