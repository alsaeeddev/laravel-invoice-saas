<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // If no business session, try to set one
        if (! session('current_business_id')) {
            $business = $user->businesses()->wherePivot('status', 'active')->first();
            if ($business) {
                session(['current_business_id' => $business->id]);
            } else {
                // No business, send to business creation
                if (! $request->routeIs('business.create') && ! $request->routeIs('business.store')) {
                    return redirect()->route('business.create');
                }
            }
        } else {
            // Verify user still belongs to this business
            $businessId = session('current_business_id');
            $belongs = $user->businesses()
                ->where('businesses.id', $businessId)
                ->wherePivot('status', 'active')
                ->exists();

            if (! $belongs) {
                session()->forget('current_business_id');
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
