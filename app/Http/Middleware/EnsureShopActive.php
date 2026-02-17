<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->shop) {
            // If user has no shop (and is not super admin), redirect or deny
            if ($user && $user->isSuperAdmin()) {
                return $next($request);
            }
            abort(403, 'User does not belong to a shop.');
        }

        $shop = $user->shop;
        
        // Allowed statuses: trial, active
        if (! in_array($shop->subscription_status, ['trial', 'active'])) {
             // In a real app, redirect to subscription renewal page
             abort(403, 'Shop subscription is ' . $shop->subscription_status . '. Please renew.');
        }

        return $next($request);
    }
}
