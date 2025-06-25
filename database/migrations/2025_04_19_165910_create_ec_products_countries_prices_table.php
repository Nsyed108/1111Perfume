<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ec_products_countries_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('ec_products')->onDelete('cascade');
            $table->unsignedBigInteger('country_id'); // Define your country reference (from a countries table or config)
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ec_products_countries_prices');
    }
};
