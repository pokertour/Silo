<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('iso_files', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });

        // Generate UUIDs for existing records
        DB::table('iso_files')->get()->each(function ($iso) {
            DB::table('iso_files')->where('id', $iso->id)->update([
                'uuid' => (string) Str::uuid(),
            ]);
        });

        // Make it non-nullable and unique
        Schema::table('iso_files', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iso_files', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
