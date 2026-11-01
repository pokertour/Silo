<?php

namespace App\Notifications;

use App\Models\IsoFile;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChecksumMismatch extends Notification
{
    use Queueable;

    public function __construct(private IsoFile $isoFile) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'iso_file_id' => $this->isoFile->id,
            'message' => __('app.Alerte : le checksum de « :name » ne correspond pas à l\'empreinte attendue. Le fichier est peut-être corrompu.', [
                'name' => $this->isoFile->name,
            ]),
            'icon' => 'alert-triangle',
        ];
    }
}
