<?php

use App\Enums\LicenseStatus;
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
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key', 64)->unique();
            $table->string('key_plain', 255)->nullable()->unique();
            $table->string('status', 16)->default(LicenseStatus::ACTIVE->value);
            $table->unsignedTinyInteger('max_major_version')->default(1);
            $table->boolean('can_access_prereleases')->default(true);
            $table->json('meta')->nullable();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();

            // Activation tracking
            $table->timestamp('activated_at')->nullable();
            $table->unsignedInteger('activation_count')->default(0);
            $table->string('device_id', 255)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
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
