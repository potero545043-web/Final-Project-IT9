<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()->latest()->paginate(12);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 403);

        // Mark as read
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        if (! empty($notification->data['item_slug'])) {
            $item = Item::where('slug', $notification->data['item_slug'])->first();
            
            if ($item) {
                return redirect()->route('items.show', $item->slug);
            }
            
            // Item no longer exists, redirect to home with message
            return redirect()->route('home')->with('warning', 'The item referenced in this notification no longer exists.');
        }

        return redirect()->route('notifications.index');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Notifications marked as read.');
    }
}
