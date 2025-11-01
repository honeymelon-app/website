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
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 50)->index();
            $table->string('tag', 50)->index();
            $table->string('commit_hash', 1024)->index();
            $table->string('channel', 16)->index();
            $table->text('notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('major')->default(false);
            $table->foreignIdFor(User::class)->nullable()->index();
            $table->timestamps();

            $table->unique(['version', 'channel', 'commit_hash', 'tag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
