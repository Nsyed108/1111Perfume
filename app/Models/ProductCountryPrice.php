<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCountryPrice extends Model
{
    protected $table = 'ec_products_countries_prices';

    protected $fillable = [
        'product_id', 'country_id', 'price', 'sale_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
