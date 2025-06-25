<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Botble\Location\Models\Country;

class PublicEcommerceController extends BaseController
{
    public function changeCurrency(Request $request, ?string $title = null)
    {
        if (empty($title)) {
            $title = $request->input('currency');
        }

        if (! $title) {
            return $this->httpResponse();
        }

        /**
         * @var Currency $currency
         */
        $currency = Currency::query()->where('title', $title)->first();

        if ($currency) {
            cms_currency()->setApplicationCurrency($currency);
        }

        $url = URL::previous();

        if (! $url || $url === URL::current()) {
            return $this
                ->httpResponse()
                ->setNextUrl(BaseHelper::getHomepageUrl());
        }

        if (Str::contains($url, ['min_price', 'max_price'])) {
            $url = preg_replace('/&min_price=[0-9]+/', '', $url);
            $url = preg_replace('/&max_price=[0-9]+/', '', $url);
        }

        return $this
            ->httpResponse()
            ->setNextUrl($url);
    }

    public function changeCountry(Request $request)
    {
        $countryId = $request->input('country_id');
        $country = Country::where('id', $countryId)->first();

        if ($country) {
            session(['country_id' => $country->id]);

            // Set matching currency for this country
            $currency = Currency::where('country_id', $country->id)->first();
            if ($currency) {
                cms_currency()->setApplicationCurrency($currency); // stored in session
            }
        }

        return redirect()->back(); // or return JSON if you want AJAX
    }
}
