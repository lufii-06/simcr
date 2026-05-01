<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProjectAssignedNotification extends Notification
{
    use Queueable;

    protected $project;
    protected $roleName;

    /**
     * Create a new notification instance.
     */
    public function __construct($project, $roleName)
    {
        $this->project = $project;
        $this->roleName = $roleName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New assignment: ' . $this->project->name . ' (' . $this->roleName . ')',
            'icon' => 'fa fa-user-plus',
            'color' => 'notif-primary',
            'url' => route('project.index'),
        ];
    }
}
