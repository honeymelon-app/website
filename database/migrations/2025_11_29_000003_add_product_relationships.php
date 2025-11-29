<?php

use App\Models\Product;
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
        Schema::table('releases', function (Blueprint $table) {
            // Add product_id column before version
            $table->foreignUuid('product_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // Add published flags
            $table->boolean('is_downloadable')->default(false)->after('published_at');
        });

        Schema::table('licenses', function (Blueprint $table) {
            // Add user_id to link licenses to users
            $table->foreignIdFor(User::class)
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();

            // Add product_id for direct product reference
            $table->foreignUuid('product_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Add prerelease access flag
            $table->boolean('can_access_prereleases')->default(true)->after('max_major_version');
        });

        Schema::table('orders', function (Blueprint $table) {
            // Add user_id to link orders to users
            $table->foreignIdFor(User::class)
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            // Add product_id
            $table->foreignUuid('product_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('releases', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'is_downloadable']);
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['user_id', 'product_id', 'can_access_prereleases']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['user_id', 'product_id']);
        });
    }
};
