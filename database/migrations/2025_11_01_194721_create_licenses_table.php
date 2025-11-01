<?php

use App\Models\Order;
use App\Enums\LicenseStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();               // e.g., XXXX-XXXX-XXXX-XXXX
            $table->enum('status', LicenseStatus::cases())->default(LicenseStatus::ACTIVE->value);   // active | revoked | expired
            $table->unsignedInteger('seats')->default(1);
            $table->json('entitlements')->nullable();          // ["pro", "hevc", ...]
            $table->timestamp('updates_until')->nullable();    // perpetual license w/ maintenance
            $table->json('meta')->nullable();                  // store signed payload, etc.
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
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
