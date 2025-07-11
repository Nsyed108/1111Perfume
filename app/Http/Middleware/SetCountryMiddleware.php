<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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

        $fixedCountryId = 4; // Iraq
        $sessionCountryId = session('country_id');
        $cookieCountryId = $request->cookie('country_id');

        if ($sessionCountryId != $fixedCountryId || $cookieCountryId != $fixedCountryId) {
            $country = Country::where('id', $fixedCountryId)
                ->where('status', 'published')
                ->with('currency')
                ->first();

            if ($country) {
                // Clear cart
                app(BaseCart::class)->destroy();

                // Set session
                session([
                    'country_id' => $country->id,
                    'country_code' => $country->code ?? null,
                    'currency' => $country->currency->title,
                    'currency_id' => $country->currency->id,
                    'currency_symbol' => $country->currency->symbol,
                ]);

                // Set cookies (5 years)
                $minutes = 60 * 24 * 365 * 5;
                Cookie::queue('country_id', $country->id, $minutes);
                Cookie::queue('country_code', $country->code ?? null, $minutes);
                Cookie::queue('currency', $country->currency->title, $minutes);
                Cookie::queue('currency_id', $country->currency->id, $minutes);
                Cookie::queue('currency_symbol', $country->currency->symbol, $minutes);
            }
        }

        return $next($request);
    }
}
