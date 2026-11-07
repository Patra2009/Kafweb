<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Restrict route access to users with one of the required roles.
     *
     * Usage: ->middleware('role:kasir,admin') or ->middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! in_array($user->role->value, $roles, true)) {
            abort(403, 'Akses ditolak: peran Anda tidak berhak mengakses endpoint ini.');
        }

        return $next($request);
    }
}
