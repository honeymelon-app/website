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
            $table->string('key', 64)->unique();               // hashed
            $table->string('key_plain', 255)->nullable()->unique();      // human-readable license key
            $table->enum('status', LicenseStatus::cases())->default(LicenseStatus::ACTIVE->value);
            $table->unsignedTinyInteger('max_major_version')->default(1);
            $table->json('meta')->nullable();
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
