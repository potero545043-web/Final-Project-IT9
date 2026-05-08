<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        $myItems = Item::query()
            ->withCount('claims')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->latest()
            ->get();

        $myClaims = Claim::query()
            ->with(['item.user'])
            ->where('claimant_id', $user->id)
            ->whereHas('item', fn ($query) => $query->where('status', '!=', 'deleted'))
            ->latest()
            ->get();

        $pendingReviews = Claim::query()
            ->with(['item', 'claimant'])
            ->where('status', 'pending')
            ->whereHas('item', function ($query) use ($user): void {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', '!=', 'deleted');
            })
            ->latest()
            ->get();

        $stats = [
            'total_reports' => Item::query()->where('status', '!=', 'deleted')->count(),
            'lost' => Item::query()->where('type', 'lost')->where('status', '!=', 'deleted')->count(),
            'found' => Item::query()->where('type', 'found')->where('status', '!=', 'deleted')->count(),
            'under_review' => Item::query()->where('status', 'under_review')->count(),
            'open_cases' => Item::query()->where('status', 'open')->count(),
            'pending_claims' => Claim::where('status', 'pending')->count(),
            'resolved_cases' => Item::query()->where('status', 'resolved')->count(),
            'my_active_reports' => Item::query()->where('user_id', $user->id)->whereIn('status', ['open', 'under_review', 'claimed'])->count(),
        ];

        if ($user->isAdmin()) {
            $stats['student_reports'] = Item::query()
                ->where('status', '!=', 'deleted')
                ->whereHas('user', fn ($query) => $query->where('role', 'student'))
                ->count();
            $pendingReviews = Claim::query()
                ->with(['item', 'claimant'])
                ->where('status', 'pending')
                ->whereHas('item', fn ($query) => $query->where('status', '!=', 'deleted'))
                ->latest()
                ->get();

            $recentItems = Item::query()->with('user')->where('status', '!=', 'deleted')->latest()->take(8)->get();
            $recentClaims = Claim::query()->with(['item', 'claimant'])->latest()->take(8)->get();
            $itemsNeedingReview = Item::query()
                ->with(['user'])
                ->withCount('claims')
                ->where('status', 'under_review')
                ->orderByDesc('claims_count')
                ->latest('updated_at')
                ->take(6)
                ->get();
            $casesCloseToResolution = Item::query()
                ->with(['user'])
                ->withCount('claims')
                ->where(function ($query): void {
                    $query->where('status', 'claimed')
                        ->orWhere(function ($nested): void {
                            $nested->where('status', 'under_review')
                                ->whereHas('claims', fn ($claimQuery) => $claimQuery->where('status', 'pending'));
                        });
                })
                ->latest('updated_at')
                ->take(6)
                ->get();

            return view('dashboard.admin', compact('myItems', 'myClaims', 'pendingReviews', 'stats', 'recentItems', 'recentClaims', 'itemsNeedingReview', 'casesCloseToResolution'));
        }

        $notificationSummary = [
            'unread' => $user->unreadNotifications()->count(),
            'recent' => $user->notifications()->latest()->take(6)->get(),
        ];

        $recentStatusUpdates = collect()
            ->merge($myItems->map(fn (Item $item) => [
                'timestamp' => $item->updated_at,
                'title' => $item->title,
                'status' => $item->status_label,
                'message' => "Your report is currently {$item->status_label}.",
                'link' => route('items.show', $item),
            ]))
            ->merge($myClaims->map(fn (Claim $claim) => [
                'timestamp' => $claim->updated_at,
                'title' => $claim->item->title,
                'status' => $claim->status_label,
                'message' => "Your claim is currently {$claim->status_label}.",
                'link' => route('items.show', $claim->item),
            ]))
            ->sortByDesc('timestamp')
            ->take(6)
            ->values();

        return view('dashboard.student', compact('myItems', 'myClaims', 'pendingReviews', 'stats', 'notificationSummary', 'recentStatusUpdates'));
    }
}
