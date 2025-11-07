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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('cerberus_id')->nullable()->unique()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('avatar_url')->nullable()->after('email');
            $table->string('organisation_id')->nullable()->after('avatar_url');
            $table->string('organisation_slug')->nullable()->after('organisation_id');
            $table->string('organisation_name')->nullable()->after('organisation_slug');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cerberus_id',
                'first_name',
                'last_name',
                'avatar_url',
                'organisation_id',
                'organisation_slug',
                'organisation_name',
            ]);

            $table->string('password')->nullable(false)->change();
        });
    }
};
