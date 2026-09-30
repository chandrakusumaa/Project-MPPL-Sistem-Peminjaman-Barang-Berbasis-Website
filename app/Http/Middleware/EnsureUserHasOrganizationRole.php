<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasOrganizationRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $organization = $request->route('organization');

        if (! $organization instanceof Organization) {
            $organization = Organization::where('slug', (string) $organization)->firstOrFail();
        }

        if (! $user->hasRoleIn($organization, $roles)) {
            abort(403, 'Anda tidak memiliki hak akses yang memadai pada organisasi ini.');
        }

        return $next($request);
    }
}
