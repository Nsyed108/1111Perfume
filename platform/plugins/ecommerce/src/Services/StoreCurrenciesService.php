<?php

namespace Botble\Ecommerce\Services;

use Botble\Ecommerce\Models\Currency;

class StoreCurrenciesService
{
    public function execute(array $currencies, array $currencies2, array $deletedCurrencies): array
    {
        if ($deletedCurrencies) {
            Currency::query()->whereIn('id', $deletedCurrencies)->delete();
        }

        foreach ($currencies as $key => $item) {
            if (! $item['title'] || ! $item['symbol']) {
                continue;
            }

            $countryGroup = $currencies2[$key];
            $countryId = collect($countryGroup)->first()['country_id'] ?? null;

            $item['country_id'] = $countryId;
            $item['title'] = mb_substr(strtoupper($item['title']), 0, 3);
            $item['symbol'] = mb_substr($item['symbol'], 0, 10);
            $item['decimals'] = $item['decimals'] < 10 && $item['decimals'] >= 0 ? $item['decimals'] : 2;

            if (count($currencies) == 1) {
                $item['is_default'] = 1;
            }

            $currency = Currency::query()->find($item['id']);

            if (! $currency) {
                Currency::query()->create($item);
            } else {
                $currency->fill($item);
                $currency->save();
            }
        }

        return [
            'error' => false,
        ];
    }
}
