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
            $table->index('name');
            $table->index('email');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('iso_files', function (Blueprint $table) {
            $table->index('name');
            $table->index('os_type');
        });

        Schema::table('share_links', function (Blueprint $table) {
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('iso_files', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['os_type']);
        });

        Schema::table('share_links', function (Blueprint $table) {
            $table->dropIndex(['token']);
        });
    }
};
