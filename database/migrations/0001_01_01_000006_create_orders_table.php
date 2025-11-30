<?php

use App\Models\User;
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
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 16);
            $table->string('external_id', 255)->index();
            $table->string('email')->index();
            $table->bigInteger('amount')->nullable();
            $table->string('currency', 8)->nullable();
            $table->json('meta')->nullable();
            $table->string('refund_id', 255)->nullable()->index();
            $table->timestamp('refunded_at')->nullable();
            $table->softDeletes();
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
