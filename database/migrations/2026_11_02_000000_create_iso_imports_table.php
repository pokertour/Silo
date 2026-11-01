<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suivi des imports depuis une URL : statut, progression, débit, erreur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('url');
            $table->string('name');
            $table->json('options')->nullable(); // métadonnées du futur IsoFile + tags
            $table->string('status', 20)->default('pending')->index();
            $table->unsignedBigInteger('bytes_downloaded')->default(0);
            $table->unsignedBigInteger('bytes_total')->nullable();
            $table->unsignedBigInteger('bytes_per_second')->nullable();
            $table->text('error')->nullable();
            $table->foreignId('iso_file_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_imports');
    }
};
