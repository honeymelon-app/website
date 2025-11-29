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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');                              // "Honeymelon"
            $table->string('slug')->unique();                    // "honeymelon"
            $table->text('description')->nullable();
            $table->string('stripe_product_id')->nullable();     // Stripe product ID
            $table->string('stripe_price_id')->nullable();       // Default Stripe price ID
            $table->unsignedInteger('price_cents')->default(0);  // Display price
            $table->string('currency', 3)->default('usd');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
