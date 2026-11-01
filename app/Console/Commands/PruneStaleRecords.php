<?php

namespace App\Console\Commands;

use App\Models\IsoImport;
use App\Models\ShareLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneStaleRecords extends Command
{
    protected $signature = 'app:prune-stale-records {--days=30 : Ancienneté minimale (en jours)}';

    protected $description = 'Supprime les liens de partage expirés, les notifications lues et les imports terminés anciens';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        // Liens de partage expirés depuis plus de N jours
        $links = ShareLink::where('expires_at', '<', $cutoff)->delete();

        // Notifications lues il y a plus de N jours
        $notifications = DB::table('notifications')
            ->whereNotNull('read_at')
            ->where('read_at', '<', $cutoff)
            ->delete();

        // Imports terminés (réussis, échoués ou annulés) depuis plus de N jours
        $imports = IsoImport::whereNotIn('status', IsoImport::ACTIVE_STATUSES)
            ->where('finished_at', '<', $cutoff)
            ->delete();

        $this->info("Purge terminée : {$links} lien(s) de partage, {$notifications} notification(s), {$imports} import(s).");

        return self::SUCCESS;
    }
}
