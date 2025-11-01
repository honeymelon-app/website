<?php

use App\Models\Release;
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
        Schema::create('updates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Release::class)->constrained()->cascadeOnDelete();
            $table->string('channel', 16)->index();            // stable | beta
            $table->string('version', 50)->index();            // redundant for quick lookups
            $table->json('manifest');                           // full Tauri manifest JSON
            $table->boolean('is_latest')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['channel', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updates');
    }
};
