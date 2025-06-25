<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCountryInventory extends Model
{
    protected $table = 'ec_products_countries_inventory';

    protected $fillable = [
        'product_id', 'country_id', 'quantity'
    ];
}
