<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Chiffre la configuration des emplacements de stockage (identifiants S3,
 * FTP, SFTP) jusqu'ici stockée en JSON clair. Le modèle utilise désormais
 * le cast « encrypted:array ».
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('storage_locations')->get(['id', 'configuration']);

        // Une valeur chiffrée n'est pas du JSON valide : la colonne devient du texte.
        Schema::table('storage_locations', function (Blueprint $table) {
            $table->text('configuration')->nullable()->change();
        });

        foreach ($rows as $row) {
            if ($row->configuration === null) {
                continue;
            }

            DB::table('storage_locations')
                ->where('id', $row->id)
                ->update(['configuration' => Crypt::encryptString($row->configuration)]);
        }
    }

    public function down(): void
    {
        $rows = DB::table('storage_locations')->get(['id', 'configuration']);

        foreach ($rows as $row) {
            if ($row->configuration === null) {
                continue;
            }

            DB::table('storage_locations')
                ->where('id', $row->id)
                ->update(['configuration' => Crypt::decryptString($row->configuration)]);
        }

        Schema::table('storage_locations', function (Blueprint $table) {
            $table->json('configuration')->nullable()->change();
        });
    }
};
