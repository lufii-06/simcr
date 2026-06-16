<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MergeRequestNotification extends Notification
{
    use Queueable;

    protected $repository;
    protected $mergeRequest;
    protected $action; // 'created', 'merged', 'closed'

    /**
     * Create a new notification instance.
     */
    public function __construct($repository, $mergeRequest, $action)
    {
        $this->repository = $repository;
        $this->mergeRequest = $mergeRequest;
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
            $msg = 'New Merge Request: ' . $this->mergeRequest->title . ' in ' . $this->repository->name;
            $icon = 'fa fa-code-branch';
            $color = 'notif-primary';
        } elseif ($this->action === 'merged') {
            $msg = 'Merge Request Merged: ' . $this->mergeRequest->title;
            $icon = 'fa fa-check-circle';
            $color = 'notif-success';
        } elseif ($this->action === 'closed') {
            $msg = 'Merge Request Closed: ' . $this->mergeRequest->title;
            $icon = 'fa fa-times-circle';
            $color = 'notif-danger';
        }

        return [
            'message' => $msg,
            'icon' => $icon,
            'color' => $color,
            'url' => route('repository.show', [
                'repository' => $this->repository->name,
                'tab' => 'pills-merges'
            ]),
        ];
    }
}
