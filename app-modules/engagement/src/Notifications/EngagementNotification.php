<?php

namespace Assist\Engagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Assist\Engagement\Models\EngagementDeliverable;

class EngagementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EngagementDeliverable $deliverable
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->deliverable->engagement->subject)
            ->greeting('Hello ' . $this->deliverable->engagement->recipient->preferred . '!')
            ->line($this->deliverable->engagement->body)
            ->salutation("Regards, {$this->deliverable->engagement->user->name}");
    }
}