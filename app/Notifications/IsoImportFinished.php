<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IsoImportFinished extends Notification
{
    use Queueable;

    public function __construct(
        private bool $success,
        private string $name,
        private ?int $isoFileId = null,
        private ?string $error = null,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'iso_file_id' => $this->isoFileId,
            'message' => $this->success
                ? __('app.L\'import de « :name » est terminé.', ['name' => $this->name])
                : __('app.L\'import de « :name » a échoué : :error', ['name' => $this->name, 'error' => $this->error ?? '?']),
            'icon' => $this->success ? 'download-cloud' : 'alert-triangle',
        ];
    }
}
