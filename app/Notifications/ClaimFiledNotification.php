<?php

namespace App\Notifications;

use App\Models\Claim;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimFiledNotification extends Notification
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
        $isOwner = method_exists($notifiable, 'getKey') && $notifiable->getKey() === $this->claim->item->user_id;

        return [
            'type' => 'claim_filed',
            'title' => $isOwner ? 'Someone submitted a claim for your item' : 'New ownership request to review',
            'message' => $isOwner
                ? "{$this->claim->claimant->name} submitted a claim for your item \"{$this->claim->item->title}\"."
                : "{$this->claim->claimant->name} submitted an ownership request for \"{$this->claim->item->title}\".",
            'item_id' => $this->claim->item_id,
            'item_slug' => $this->claim->item->slug,
            'claim_id' => $this->claim->id,
            'item_title' => $this->claim->item->title,
            'claimant_name' => $this->claim->claimant->name,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isOwner = method_exists($notifiable, 'getKey') && $notifiable->getKey() === $this->claim->item->user_id;

        return (new MailMessage)
            ->subject($isOwner ? 'Someone Submitted a Claim for Your Item' : 'New Ownership Request to Review')
            ->greeting("Hello {$notifiable->name},")
            ->line($isOwner
                ? "Someone submitted a claim for your item \"{$this->claim->item->title}\"."
                : "A new ownership request was submitted for \"{$this->claim->item->title}\".")
            ->line("Claimant: {$this->claim->claimant->name}")
            ->action('Review Item', route('items.show', $this->claim->item))
            ->line('Please review the ownership proof and update the claim decision.');
    }
}
