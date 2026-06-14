<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MergeRequestNotification extends Notification
{
    use Queueable;

    protected $repository;
    protected $pullRequest;
    protected $action; // 'created', 'merged', 'closed'

    /**
     * Create a new notification instance.
     */
    public function __construct($repository, $pullRequest, $action)
    {
        $this->repository = $repository;
        $this->pullRequest = $pullRequest;
        $this->action = $action;
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
        $msg = '';
        $icon = 'fa fa-code-branch';
        $color = 'notif-success';
        if ($this->action === 'created') {
            $msg = 'New Merge Request: ' . $this->pullRequest->title . ' in ' . $this->repository->name;
            $icon = 'fa fa-code-branch';
            $color = 'notif-primary';
        } elseif ($this->action === 'merged') {
            $msg = 'Merge Request Merged: ' . $this->pullRequest->title;
            $icon = 'fa fa-check-circle';
            $color = 'notif-success';
        } elseif ($this->action === 'closed') {
            $msg = 'Merge Request Closed: ' . $this->pullRequest->title;
            $icon = 'fa fa-times-circle';
            $color = 'notif-danger';
        }

        return [
            'message' => $msg,
            'icon' => $icon,
            'color' => $color,
            'url' => route('repository.show', [
                'repository' => $this->repository->name,
                'tab' => 'pills-pulls'
            ]),
        ];
    }
}
