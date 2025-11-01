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
        Schema::create('artifacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('release_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 64)->index();                // e.g., darwin-aarch64
            $table->string('source', 16)->default('github');        // github | r2 | s3
            $table->string('filename')->nullable();                 // display name
            $table->unsignedBigInteger('size')->nullable();
            $table->string('sha256', 128)->nullable();
            $table->string('signature', 512)->nullable();           // Tauri ed25519 signature
            $table->boolean('notarized')->default(false);
            $table->string('url')->nullable();                      // for github source
            $table->string('path')->nullable();                     // for r2/s3 source
            $table->timestamps();

            $table->unique(['release_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artifacts');
    }
};
