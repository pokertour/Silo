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
        Schema::table('iso_files', function (Blueprint $table) {
            $table->string('version')->nullable()->after('name');
            $table->string('architecture')->nullable()->after('version');
            $table->string('os_type')->nullable()->after('architecture');
            $table->text('description')->nullable()->after('os_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iso_files', function (Blueprint $table) {
            $table->dropColumn(['version', 'architecture', 'os_type', 'description']);
        });
    }
};
