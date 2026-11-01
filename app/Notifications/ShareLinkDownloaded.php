<?php

namespace App\Notifications;

use App\Models\ShareLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShareLinkDownloaded extends Notification
{
    use Queueable;

    public function __construct(private ShareLink $shareLink) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $count = $this->shareLink->download_count;
        $max = $this->shareLink->max_downloads;

        return [
            'share_link_id' => $this->shareLink->id,
            'iso_file_id' => $this->shareLink->iso_file_id,
            'message' => __('app.Votre lien de partage pour « :name » a été téléchargé (:count:max).', [
                'name' => $this->shareLink->isoFile?->name ?? '?',
                'count' => $count,
                'max' => $max ? " / {$max}" : '',
            ]),
            'icon' => 'download',
        ];
    }
}
