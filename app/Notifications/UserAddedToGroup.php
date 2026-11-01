<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserAddedToGroup extends Notification
{
    use Queueable;

    private $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'group_id' => $this->group->id,
            'group_name' => $this->group->name,
            'message' => "Vous avez été ajouté au groupe : {$this->group->name}",
            'icon' => 'users',
        ];
    }
}
