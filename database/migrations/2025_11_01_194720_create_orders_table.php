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
        Schema::create('orders', function (Blueprint $table) {
            $table->string('provider', 16);                    // ls | stripe
            $table->string('external_id', 64)->index();        // provider-side id
            $table->string('email')->index();
            $table->bigInteger('amount')->nullable();
            $table->string('currency', 8)->nullable();
            $table->json('meta')->nullable();                  // raw webhook attrs
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
