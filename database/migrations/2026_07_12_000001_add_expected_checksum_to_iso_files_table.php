<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iso_files', function (Blueprint $table) {
            $table->string('expected_checksum')->nullable()->after('checksum');
            // null = pas de checksum attendu, true/false = résultat de la comparaison
            $table->boolean('checksum_verified')->nullable()->after('expected_checksum');
        });
    }

    public function down(): void
    {
        Schema::table('iso_files', function (Blueprint $table) {
            $table->dropColumn(['expected_checksum', 'checksum_verified']);
        });
    }
};
