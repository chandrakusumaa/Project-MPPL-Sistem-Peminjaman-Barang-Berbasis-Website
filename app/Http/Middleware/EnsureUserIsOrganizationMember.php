<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOrganizationMember
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $organization = $request->route('organization');

        if (! $organization instanceof Organization) {
            $organization = Organization::where('slug', (string) $organization)->firstOrFail();
        }

        if (! $user->isMemberOf($organization)) {
            abort(403, 'Anda bukan anggota organisasi ini.');
        }

        return $next($request);
    }
}
