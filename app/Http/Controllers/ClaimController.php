<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use App\Notifications\ClaimFiledNotification;
use App\Notifications\ClaimStatusUpdatedNotification;
use App\Notifications\ItemStatusUpdatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClaimController extends Controller
{
    public function store(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'proof_details' => ['required', 'string', 'max:2000'],
            'proof_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        abort_if($item->user_id === Auth::id(), 403);

        $existingClaim = Claim::where('item_id', $item->id)
            ->where('claimant_id', Auth::id())
            ->first();

        if ($existingClaim && in_array($existingClaim->status, ['pending', 'approved'], true)) {
            return back()->with('success', 'You already have an ownership request for this item. Check the current status below.');
        }

        $proofImageUrl = $existingClaim?->proof_image_url;

        if ($request->hasFile('proof_image')) {
            $this->deleteUploadedProof($proofImageUrl);

            $proofImageUrl = $request->file('proof_image')->store('claim-proofs', 'public');
        }

        Claim::updateOrCreate(
            [
                'item_id' => $item->id,
                'claimant_id' => Auth::id(),
            ],
            [
                'message' => $validated['message'],
                'proof_details' => $validated['proof_details'],
                'proof_image_url' => $proofImageUrl,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'status' => 'pending',
                'review_notes' => null,
            ],
        );

        $claim = Claim::query()
            ->with(['item', 'claimant'])
            ->where('item_id', $item->id)
            ->where('claimant_id', Auth::id())
            ->firstOrFail();

        $item->user->notify(new ClaimFiledNotification($claim));

        User::query()
            ->where('role', 'admin')
            ->where('id', '!=', $item->user_id)
            ->get()
            ->each(fn (User $admin) => $admin->notify(new ClaimFiledNotification($claim)));

        return back()->with('success', 'Your claim is now pending review.');
    }

    public function update(Request $request, mixed $claim): RedirectResponse
    {
        $claimId = $this->resolveClaimId($claim, $request);
        $claimRecord = Claim::query()
            ->with(['item', 'claimant'])
            ->findOrFail($claimId);
        $item = $claimRecord->item;
        $user = Auth::user();

        abort_unless($user->isAdmin() || $item->user_id === $user->id, 403);

        if (! $user->isAdmin()) {
            return $this->recordFinderFeedback($request, $claimRecord);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Claim::query()
            ->whereKey($claimId)
            ->update($validated);

        if ($validated['status'] === 'approved') {
            Claim::query()
                ->where('item_id', $item->id)
                ->where('id', '!=', $claimId)
                ->update(['status' => 'rejected']);

            Item::query()
                ->whereKey($item->id)
                ->update(['status' => 'claimed']);

            if (Auth::id() !== $item->user_id) {
                $item->user->notify(
                    new ItemStatusUpdatedNotification(Item::query()->findOrFail($item->id), Auth::user())
                );
            }
        }

        $updatedClaim = Claim::query()
            ->with(['item', 'claimant'])
            ->findOrFail($claimId);

        $updatedClaim->claimant->notify(new ClaimStatusUpdatedNotification($updatedClaim));

        return back()->with('success', 'Claim status updated to '.$updatedClaim->status_label.'.');
    }

    private function recordFinderFeedback(Request $request, Claim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'finder_feedback' => ['required', Rule::in(['confirmed', 'doubted'])],
            'finder_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $claim->update($validated);

        $message = $claim->finder_feedback === 'confirmed'
            ? 'Thanks. Your confirmation was sent to the admin for final review.'
            : 'Thanks. Your concern was sent to the admin for final review.';

        return back()->with('success', $message);
    }

    private function resolveClaimId(mixed $claim, Request $request): int
    {
        if ($claim instanceof Claim) {
            return (int) $claim->getKey();
        }

        return match (true) {
            is_numeric($claim) => (int) $claim,
            is_object($claim) && isset($claim->id) => (int) $claim->id,
            default => (int) $request->route('claim'),
        };
    }

    private function deleteUploadedProof(?string $proofImageUrl): void
    {
        if (! $proofImageUrl) {
            return;
        }

        $path = Str::startsWith($proofImageUrl, ['http://', 'https://'])
            ? Str::after(parse_url($proofImageUrl, PHP_URL_PATH) ?? '', '/storage/')
            : ltrim($proofImageUrl, '/');

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Part 8: Integration of Stored Procedures and Views
     * 
     * This method demonstrates how to use stored procedures and database views
     * in your Laravel application with the LostReport and User schema.
     * These examples show the integration techniques described in the Draft.pdf.
     */
    public function demonstrateStoredProceduresAndViews()
    {
        $reports = DB::select('CALL show_lost_reports()');

        $userReports = DB::select("CALL filter_reports_by_user('Ana')");

        DB::statement("CALL update_report_status(1, 'Resolved')");

        $activeReports = DB::select('SELECT * FROM view_active_lost_reports');

        return [
            'all_reports' => $reports,
            'user_reports' => $userReports,
            'active_reports' => $activeReports,
        ];
    }
}

