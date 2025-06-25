<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\Currency;
use Botble\Location\Models\Country;
use Botble\Ecommerce\Cart\Cart as BaseCart;

class SetCountryMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Skip admin panel
        $adminPrefix = \BaseHelper::getAdminPrefix();
        if ($request->is($adminPrefix) || $request->is($adminPrefix . '/*')) {
            return $next($request);
        }

        // Hardcoded country ID for Iraq
        $fixedCountryId = 4;
        $sessionCountryId = session('country_id');

        if ($sessionCountryId != $fixedCountryId) {
            $country = Country::where('id', $fixedCountryId)
                ->where('status', 'published')
                ->with('currency')
                ->first();

            if ($country) {
                // Reset cart and update session
                app(BaseCart::class)->destroy();

                session([
                    'country_id' => $country->id,
                    'country_code' => $country->code ?? null,
                    'currency' => $country->currency->title,
                    'currency_id' => $country->currency->id,
                    'currency_symbol' => $country->currency->symbol,
                ]);
            }
        }

        return $next($request);
    }
}
