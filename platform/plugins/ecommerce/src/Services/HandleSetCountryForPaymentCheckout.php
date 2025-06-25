<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Location\Models\Country;
use Illuminate\Support\Arr;

class HandleSetCountryForPaymentCheckout
{
    public function execute(array $sessionCheckoutData): void
    {
        add_filter('payment_checkout_country', function ($default) use ($sessionCheckoutData) {
            // load from session first
            $cookieCountryId = request()->cookie('country_id');
            if ($cookieCountryId) {
                $country = Country::query()
                    ->where('id', $cookieCountryId)
                    ->value('code');
                return $country;
            }

            if ($country = Arr::get($sessionCheckoutData, 'country')) {
                // load from plugin
                if (EcommerceHelper::loadCountriesStatesCitiesFromPluginLocation()) {
                    $country = Country::query()
                        ->where('id', $country)
                        ->value('code');
                }
                return $country;
            }

            return $default;
        }, 999);
    }
}
