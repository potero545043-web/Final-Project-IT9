<?php

namespace App\Notifications;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Claim $claim,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'claim_status_updated',
            'title' => $this->claim->status === 'approved'
                ? "Your claim for {$this->claim->item->title} was approved"
                : 'Your claim status changed',
            'message' => "Your claim for {$this->claim->item->title} is now {$this->claim->status_label}.",
            'item_id' => $this->claim->item_id,
            'item_slug' => $this->claim->item->slug,
            'claim_id' => $this->claim->id,
            'item_title' => $this->claim->item->title,
            'status' => $this->claim->status,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Lost and Found Claim Was Updated')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your claim for \"{$this->claim->item->title}\" is now {$this->claim->status_label}.")
            ->when($this->claim->review_notes, fn (MailMessage $mail) => $mail->line("Review notes: {$this->claim->review_notes}"))
            ->action('View Claim', route('items.show', $this->claim->item));
    }
}
