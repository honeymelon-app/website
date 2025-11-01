<?php

use App\Enums\LicenseStatus;
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
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 64)->unique();               // e.g., XXXX-XXXX-XXXX-XXXX
            $table->enum('status', LicenseStatus::cases())->default(LicenseStatus::ACTIVE->value);   // active | revoked | expired
            $table->unsignedInteger('seats')->default(1);
            $table->json('entitlements')->nullable();          // ["pro", "hevc", ...]
            $table->timestamp('updates_until')->nullable();    // perpetual license w/ maintenance
            $table->json('meta')->nullable();                  // store signed payload, etc.
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
