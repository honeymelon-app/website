<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration removes Cerberus IAM columns if they exist (for existing installations)
     * and ensures the users table is in the expected state for native Laravel auth.
     */
    public function up(): void
    {
        // Drop Cerberus-specific columns if they exist
        $cerberusColumns = [
            'cerberus_id',
            'first_name',
            'last_name',
            'avatar_url',
            'organisation_id',
            'organisation_slug',
            'organisation_name',
        ];

        Schema::table('users', function (Blueprint $table) use ($cerberusColumns) {
            $existingColumns = Schema::getColumnListing('users');
            $columnsToDrop = array_intersect($cerberusColumns, $existingColumns);

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't restore Cerberus columns on rollback as the package is removed
    }
};
