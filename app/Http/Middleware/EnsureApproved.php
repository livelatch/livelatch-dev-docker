<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

/**
 * Alpha gate. Users holding the `not_approved` role (assigned to every new
 * sign-up) are bounced to the holding page (`pending`) and kept out of the
 * dashboard / studio / admin until an admin removes the role in Manage Users.
 *
 * Existing users never hold this role, so they pass through untouched.
 */
class EnsureApproved
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Admin kill-switch: when alpha gating is off, nobody is held — even
        // existing not_approved holders flow straight through. Defaults to ON so
        // the gate is active until an admin explicitly disables it.
        if (!AppSetting::getBool('alpha_gate_enabled', true)) {
            return $next($request);
        }

        if ($user && $user->hasRole(Role::NOT_APPROVED)) {
            // The holding page itself and signing out must stay reachable,
            // otherwise the user is trapped with no way off the page.
            if ($request->routeIs('pending', 'logout') || $request->is('logout')) {
                return $next($request);
            }

            // HTMX swaps need a client-side redirect header to navigate fully.
            if ($request->header('HX-Request')) {
                return response('', 204)->header('HX-Redirect', route('pending'));
            }

            return redirect()->route('pending');
        }

        return $next($request);
    }
}
