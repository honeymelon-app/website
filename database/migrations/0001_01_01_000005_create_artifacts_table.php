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
            $table->unsignedBigInteger('github_id')->nullable()->unique();
            $table->foreignUuid('release_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 64)->index();
            $table->string('source', 16)->default('github');
            $table->string('state')->nullable();
            $table->string('filename')->nullable();
            $table->string('content_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedBigInteger('download_count')->default(0);
            $table->string('sha256', 128)->nullable();
            $table->string('signature', 512)->nullable();
            $table->boolean('notarized')->default(false);
            $table->string('url')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('github_updated_at')->nullable();
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
