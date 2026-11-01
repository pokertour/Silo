<?php

namespace App\Notifications;

use App\Models\IsoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DuplicateIsoDetected extends Notification
{
    use Queueable;

    public function __construct(
        private IsoFile $isoFile,
        private IsoFile $duplicateOf,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'iso_file_id' => $this->isoFile->id,
            'duplicate_of_id' => $this->duplicateOf->id,
            'message' => "Le fichier « {$this->isoFile->name} » est identique à « {$this->duplicateOf->name} » (même SHA-256).",
            'icon' => 'copy',
        ];
    }
}
