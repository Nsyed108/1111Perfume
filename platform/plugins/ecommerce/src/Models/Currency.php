<?php

namespace Botble\Ecommerce\Models;

use Botble\Base\Models\BaseModel;

class Currency extends BaseModel
{
    protected $table = 'ec_currencies';

    protected $fillable = [
        'title',
        'symbol',
        'country_id',
        'is_prefix_symbol',
        'order',
        'decimals',
        'is_default',
        'exchange_rate',
    ];

    protected $casts = [
        'is_prefix_symbol' => 'boolean',
        'is_default' => 'boolean',
        'exchange_rate' => 'double',
    ];

    public function country()
    {
        return $this->belongsTo(\Botble\Location\Models\Country::class, 'country_id');
    }
}
