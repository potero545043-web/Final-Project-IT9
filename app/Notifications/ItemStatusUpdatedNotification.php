<?php

namespace App\Notifications;

use App\Models\Item;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Item $item,
        private readonly ?User $actor = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $isAdminToAdminUpdate = method_exists($notifiable, 'isAdmin')
            && $notifiable->isAdmin()
            && $this->actor?->isAdmin()
            && method_exists($notifiable, 'getKey')
            && $this->actor->getKey() !== $notifiable->getKey();

        return [
            'type' => 'item_status_updated',
            'title' => $isAdminToAdminUpdate
                ? 'Another admin updated your report'
                : "Your report status changed to {$this->item->status_label}",
            'message' => $isAdminToAdminUpdate
                ? "{$this->actor?->name} updated your report \"{$this->item->title}\" to {$this->item->status_label}."
                : "Your report \"{$this->item->title}\" is now {$this->item->status_label}.",
            'item_id' => $this->item->id,
            'item_slug' => $this->item->slug,
            'item_title' => $this->item->title,
            'status' => $this->item->status,
            'actor_name' => $this->actor?->name,
            'actor_role' => $this->actor?->role,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isAdminToAdminUpdate = method_exists($notifiable, 'isAdmin')
            && $notifiable->isAdmin()
            && $this->actor?->isAdmin()
            && method_exists($notifiable, 'getKey')
            && $this->actor->getKey() !== $notifiable->getKey();

        return (new MailMessage)
            ->subject($isAdminToAdminUpdate ? 'Another Admin Updated Your Report' : 'Lost and Found Item Status Updated')
            ->greeting("Hello {$notifiable->name},")
            ->line($isAdminToAdminUpdate
                ? "{$this->actor?->name} updated your report \"{$this->item->title}\" to {$this->item->status_label}."
                : "The status for your report \"{$this->item->title}\" is now {$this->item->status_label}.")
            ->action('View Report', route('items.show', $this->item));
    }
}
