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
        Schema::table('licenses', function (Blueprint $table) {
            if (! Schema::hasColumn('licenses', 'max_major_version')) {
                $table->unsignedTinyInteger('max_major_version')->default(1)->after('status');
            }

            if (Schema::hasColumn('licenses', 'seats')) {
                $table->dropColumn('seats');
            }

            if (Schema::hasColumn('licenses', 'entitlements')) {
                $table->dropColumn('entitlements');
            }

            if (Schema::hasColumn('licenses', 'updates_until')) {
                $table->dropColumn('updates_until');
            }
        });

        if (Schema::hasTable('activations')) {
            Schema::drop('activations');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            if (Schema::hasColumn('licenses', 'max_major_version')) {
                $table->dropColumn('max_major_version');
            }

            if (! Schema::hasColumn('licenses', 'seats')) {
                $table->unsignedInteger('seats')->default(1);
            }

            if (! Schema::hasColumn('licenses', 'entitlements')) {
                $table->json('entitlements')->nullable();
            }

            if (! Schema::hasColumn('licenses', 'updates_until')) {
                $table->timestamp('updates_until')->nullable();
            }
        });

        if (! Schema::hasTable('activations')) {
            Schema::create('activations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('license_id')->constrained()->cascadeOnDelete();
                $table->string('device_id_hash');
                $table->string('app_version')->nullable();
                $table->string('os_version')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }
    }
};
