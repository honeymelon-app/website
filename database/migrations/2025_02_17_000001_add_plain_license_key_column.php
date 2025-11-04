<?php

use App\Support\LicenseCodec;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('licenses', 'key_plain')) {
            return;
        }

        Schema::table('licenses', function (Blueprint $table): void {
            $table->string('key_plain', 255)->nullable()->after('key');
        });

        DB::table('licenses')->select(['id', 'key'])->orderBy('id')->chunkById(100, function ($licenses): void {
            foreach ($licenses as $license) {
                $plain = (string) $license->key;
                $normalized = LicenseCodec::normalize($plain);
                $hashed = hash('sha256', $normalized);

                DB::table('licenses')
                    ->where('id', $license->id)
                    ->update([
                        'key_plain' => $plain,
                        'key' => $hashed,
                    ]);
            }
        }, 'id');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('licenses', 'key_plain')) {
            return;
        }

        Schema::table('licenses', function (Blueprint $table): void {
            $table->dropColumn('key_plain');
        });
    }
};
