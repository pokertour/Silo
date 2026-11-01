<?php

namespace App\Console\Commands;

use App\Models\ShareLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneStaleRecords extends Command
{
    protected $signature = 'app:prune-stale-records {--days=30 : Ancienneté minimale (en jours)}';

    protected $description = 'Supprime les liens de partage expirés et les notifications lues anciennes';

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

        $this->info("Purge terminée : {$links} lien(s) de partage, {$notifications} notification(s).");

        return self::SUCCESS;
    }
}
