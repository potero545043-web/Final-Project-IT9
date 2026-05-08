<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\User;
use App\Notifications\ItemStatusUpdatedNotification;
use App\Notifications\NewReportSubmittedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'status', 'category', 'q']);

        $items = Item::query()
            ->active()
            ->with(['user', 'claims'])
            ->filter($filters)
            ->latest('reported_at')
            ->paginate(9)
            ->withQueryString();

        $stats = [
            'lost' => Item::query()->where('type', 'lost')->active()->count(),
            'found' => Item::query()->where('type', 'found')->active()->count(),
            'resolved' => Item::query()->where('status', 'resolved')->count(),
        ];

        return view('home', [
            'items' => $items,
            'filters' => $filters,
            'stats' => $stats,
            'categories' => Item::categories(),
            'statuses' => Item::statuses(),
        ]);
    }

    public function create(): View
    {
        return view('items.create', [
            'categories' => Item::categories(),
            'statuses' => Item::statuses(),
            'canEditStatus' => false,
        ]);
    }

    public function myReports(): View
    {
        $user = Auth::user();

        $allItems = Item::query()
            ->where('user_id', $user->id)
            ->where('status', '!=', 'deleted')
            ->get();

        $items = Item::query()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['resolved', 'deleted'])
            ->withCount('claims')
            ->latest('reported_at')
            ->get();

        $stats = [
            'total' => $allItems->count(),
            'under_review' => $allItems->where('status', 'under_review')->count(),
            'open' => $allItems->where('status', 'open')->count(),
            'resolved' => $allItems->where('status', 'resolved')->count(),
            'archived' => Item::query()->where('user_id', $user->id)->archived()->count(),
        ];

        return view('items.my-reports', compact('items', 'stats'));
    }

    public function archivedReports(Request $request): View
    {
        $user = Auth::user();
        $filters = $request->only(['q', 'sort']);

        $items = Item::query()
            ->archived()
            ->with(['user', 'deletedBy'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(
                ($filters['sort'] ?? 'deleted_desc') === 'deleted_asc',
                fn ($query) => $query->orderBy('deleted_at'),
                function ($query) use ($filters): void {
                    match ($filters['sort'] ?? 'deleted_desc') {
                        'id_asc' => $query->orderBy('id'),
                        'id_desc' => $query->orderByDesc('id'),
                        default => $query->orderByDesc('deleted_at'),
                    };
                }
            )
            ->paginate(10)
            ->withQueryString();

        $baseArchiveQuery = Item::query()
            ->archived()
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id));

        $stats = [
            'total' => (clone $baseArchiveQuery)->count(),
            'lost' => (clone $baseArchiveQuery)->where('type', 'lost')->count(),
            'found' => (clone $baseArchiveQuery)->where('type', 'found')->count(),
            'filtered' => $items->total(),
        ];

        return view('items.archived', [
            'items' => $items,
            'filters' => $filters,
            'stats' => $stats,
            'title' => $user->isAdmin() ? 'Deleted Reports' : 'My Deleted Reports',
        ]);
    }

    public function resolvedReports(Request $request): View
    {
        $user = Auth::user();
        $filters = $request->only(['q', 'sort']);

        $items = Item::query()
            ->where('status', 'resolved')
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->with(['user', 'approvedClaim.claimant'])
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(
                ($filters['sort'] ?? 'resolved_desc') === 'resolved_asc',
                fn ($query) => $query->orderBy('updated_at'),
                function ($query) use ($filters): void {
                    match ($filters['sort'] ?? 'resolved_desc') {
                        'id_asc' => $query->orderBy('id'),
                        'id_desc' => $query->orderByDesc('id'),
                        default => $query->orderByDesc('updated_at'),
                    };
                }
            )
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Item::where('status', 'resolved')
                ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
                ->count(),
            'claimed' => Item::where('status', 'resolved')
                ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
                ->whereHas('approvedClaim')
                ->count(),
            'unclaimed' => Item::where('status', 'resolved')
                ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
                ->whereDoesntHave('approvedClaim')
                ->count(),
            'filtered' => $items->total(),
        ];

        return view('items.resolved', [
            'items' => $items,
            'filters' => $filters,
            'stats' => $stats,
            'title' => 'Resolved Reports',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $user = Auth::user();

        if ($request->hasFile('photo')) {
            $data['photo_url'] = $request->file('photo')->store('item-photos', 'public');
        }

        $item = Item::create([
            ...$data,
            'user_id' => $user->id,
            'contact_name' => $data['contact_name'] ?? $user->name,
            'contact_email' => $data['contact_email'] ?? $user->email,
            'contact_phone' => $data['contact_phone'] ?? $user->phone,
        ]);

        if (! $user->isAdmin()) {
            User::query()
                ->where('role', 'admin')
                ->get()
                ->each(fn (User $admin) => $admin->notify(new NewReportSubmittedNotification($item->fresh('user'))));
        }

        return redirect()
            ->route('items.confirmation', $item)
            ->with('success', 'Your report was submitted successfully.');
    }

    public function confirmation(Item $item): View
    {
        abort_unless($this->canManage($item), 403);

        return view('items.confirmation', ['item' => $item]);
    }

    public function show(Item $item): View
    {
        $viewerId = Auth::id();
        $ownsItem = $viewerId !== null && (int) $viewerId === (int) $item->user_id;

        abort_if(
            $item->status === 'deleted' && (! Auth::check() || (! Auth::user()->isAdmin() && ! $ownsItem)),
            404
        );

        $item->load(['user', 'claims.claimant']);
        $viewerClaim = Auth::check()
            ? $item->claims->firstWhere('claimant_id', Auth::id())
            : null;

        return view('items.show', [
            'item' => $item,
            'viewerClaim' => $viewerClaim,
        ]);
    }

    public function edit(Item $item): View
    {
        abort_unless($this->canManage($item), 403);
        abort_if(in_array($item->status, ['resolved', 'deleted'], true) && ! Auth::user()->isAdmin(), 403);
        abort_if($item->status === 'deleted', 403);

        return view('items.edit', [
            'item' => $item,
            'categories' => Item::categories(),
            'statuses' => Item::statuses(),
            'canEditStatus' => Auth::user()->isAdmin(),
        ]);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        abort_unless($this->canManage($item), 403);
        abort_if(in_array($item->status, ['resolved', 'deleted'], true) && ! Auth::user()->isAdmin(), 403);
        abort_if($item->status === 'deleted', 403);

        $data = $this->validatedData($request, $item);

        if (! Auth::user()->isAdmin()) {
            $data['status'] = $item->status;
        }

        if ($request->hasFile('photo')) {
            $this->deleteUploadedPhoto($item->photo_url);

            $data['photo_url'] = $request->file('photo')->store('item-photos', 'public');
        }

        $item->update($data);

        return redirect()->route('items.show', $item)->with('success', 'Your report details were updated successfully.');
    }

    public function updateStatus(Request $request, Item $item): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        abort_if($item->status === 'deleted', 403);

        $originalStatus = $item->status;
        $validated = $request->validate([
            'status' => ['required', Rule::in(Item::statuses())],
            'resolution_type' => ['nullable', Rule::in(['returned_to_owner', 'unclaimed_closed', 'invalid_report', 'other'])],
        ]);

        $updateData = ['status' => $validated['status']];

        // Add resolution metadata when marking as resolved
        if ($validated['status'] === 'resolved') {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = Auth::id();
            if ($validated['resolution_type'] ?? null) {
                $updateData['resolution_type'] = $validated['resolution_type'];
            }
        }

        $item->update($updateData);

        if ($originalStatus !== $validated['status']) {
            $freshItem = $item->fresh();
            $actor = Auth::user();

            if (Auth::id() !== $item->user_id) {
                $item->user->notify(new ItemStatusUpdatedNotification($freshItem, $actor));
            }

            if ($item->user->isAdmin()) {
                User::query()
                    ->where('role', 'admin')
                    ->where('id', '!=', Auth::id())
                    ->where('id', '!=', $item->user_id)
                    ->get()
                    ->each(fn (User $admin) => $admin->notify(new ItemStatusUpdatedNotification($freshItem, $actor)));
            }
        }

        return back()->with('success', 'Item status updated to '.$item->fresh()->status_label.'.');
    }

    public function destroy(Item $item): RedirectResponse
    {
        abort_unless($this->canManage($item), 403);
        abort_if($item->status === 'resolved' && ! Auth::user()->isAdmin(), 403);
        abort_if($item->status === 'deleted', 403);

        $actor = Auth::user();

        $item->update([
            'status' => 'deleted',
            'deleted_at' => now(),
            'deleted_by' => $actor?->id,
            'archived_from_status' => $item->status,
        ]);

        Log::info('Lost and found report archived.', [
            'item_id' => $item->id,
            'item_title' => $item->title,
            'archived_by_user_id' => $actor?->id,
            'archived_by_name' => $actor?->name,
            'report_owner' => $item->user?->name,
            'archived_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('items.mine')->with('success', 'Report archived successfully.');
    }

    private function validatedData(Request $request, ?Item $item = null): array
    {
        $statusRules = [
            $item && Auth::user()?->isAdmin() ? 'required' : 'nullable',
            Rule::in(Item::statuses()),
        ];

        $validated = $request->validate([
            'type' => ['required', Rule::in(['lost', 'found'])],
            'category' => ['required', Rule::in(Item::categories())],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
            'reported_at' => ['required', 'date'],
            'status' => $statusRules,
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'reward_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        if (! $item) {
            $validated['status'] = 'under_review';
        } elseif (! Auth::user()?->isAdmin()) {
            $validated['status'] = $item->status;
        }

        if ($validated['type'] === 'found') {
            $validated['reward_amount'] = null;
        }

        return $validated;
    }

    private function deleteUploadedPhoto(?string $photoUrl): void
    {
        if (! $photoUrl) {
            return;
        }

        $path = Str::startsWith($photoUrl, ['http://', 'https://'])
            ? Str::after(parse_url($photoUrl, PHP_URL_PATH) ?? '', '/storage/')
            : ltrim($photoUrl, '/');

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function canManage(Item $item): bool
    {
        $user = Auth::user();

        return $user->isAdmin() || (int) $item->user_id === (int) $user->id;
    }
}
