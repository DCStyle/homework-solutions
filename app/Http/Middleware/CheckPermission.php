<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (Auth::check() && Auth::user()->hasPermission($permission)) {
            return $next($request);
        } else {
            // If the user is not authenticated, treat them as a "Guest"
            $guestRole = Role::where('name', 'Guest')->first();
            if ($guestRole && $guestRole->permissions->contains('name', $permission)) {
                return $next($request);
            }
        }

        return redirect('/')->with('error', 'You do not have permission to view this page.');
    }
}
