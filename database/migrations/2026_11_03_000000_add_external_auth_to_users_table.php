<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // null = compte local ; « ldap » ou « oidc » = compte créé par l'annuaire / le SSO
            $table->string('auth_provider', 20)->nullable()->after('password');
            // Identifiant stable chez le fournisseur (DN LDAP, « sub » OIDC)
            $table->string('external_id')->nullable()->after('auth_provider');
            $table->unique(['auth_provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['auth_provider', 'external_id']);
            $table->dropColumn(['auth_provider', 'external_id']);
        });
    }
};
