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
        Schema::dropIfExists('updates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('release_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 16)->index();
            $table->string('version', 50)->index();
            $table->json('manifest');
            $table->boolean('is_latest')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['channel', 'version']);
        });
    }
};
