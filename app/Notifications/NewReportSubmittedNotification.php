<?php

namespace App\Notifications;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReportSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Item $item,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_report_submitted',
            'title' => 'New report submitted by user',
            'message' => "{$this->item->user->name} submitted a new {$this->item->type} report for \"{$this->item->title}\".",
            'item_id' => $this->item->id,
            'item_slug' => $this->item->slug,
            'item_title' => $this->item->title,
            'reporter_name' => $this->item->user->name,
            'status' => $this->item->status,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Lost and Found Report Submitted')
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->item->user->name} submitted a new {$this->item->type} report for \"{$this->item->title}\".")
            ->action('Review Report', route('items.show', $this->item));
    }
}
