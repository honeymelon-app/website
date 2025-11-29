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
            $table->string('platform', 64)->index();
            $table->string('source', 16)->default('github');
            $table->string('filename')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('sha256', 128)->nullable();
            $table->string('signature', 512)->nullable();
            $table->boolean('notarized')->default(false);
            $table->string('url')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
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
