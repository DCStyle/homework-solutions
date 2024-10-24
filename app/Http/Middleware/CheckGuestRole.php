<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckGuestRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // For non-authenticated users (guests), we can dynamically treat them as "Guest"
            $guestRole = Role::where('name', 'Guest')->first();

            if ($guestRole && !$request->user()) {
                // Dynamically assigning a guest-like permission check
                $request->merge(['guestRole' => $guestRole]);
            }
        }

        return $next($request);
    }
}
