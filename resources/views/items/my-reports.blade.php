@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top summary-strip-five">
        <article class="summary-pill summary-pill-blue">
            <div>
                <p class="summary-pill-label">My Reports</p>
                <p class="summary-pill-text">All reports you personally submitted</p>
                <p class="summary-pill-value">{{ $stats['total'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <p class="summary-pill-label">Under Review</p>
                <p class="summary-pill-text">Reports still waiting for approval</p>
                <p class="summary-pill-value">{{ $stats['under_review'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <p class="summary-pill-label">Open</p>
                <p class="summary-pill-text">Reports currently active in the system</p>
                <p class="summary-pill-value">{{ $stats['open'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <p class="summary-pill-label">Resolved</p>
                <p class="summary-pill-text">Reports that have already been completed</p>
                <p class="summary-pill-value">{{ $stats['resolved'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <p class="summary-pill-label">Deleted</p>
                <p class="summary-pill-text">Reports you removed from your active workspace</p>
                <p class="summary-pill-value">{{ $stats['archived'] }}</p>
            </div>
        </article>
    </section>

    <section class="console-panel mt-6">
        <div class="console-panel-header">
            <div>
                <p class="console-label">Private Workspace</p>
                <h2 class="console-title">My reports</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('items.resolved') }}" class="secondary-button">Resolved Reports</a>
                <a href="{{ route('items.archived') }}" class="secondary-button">My Deleted Reports</a>
                <a href="{{ route('items.create') }}" class="primary-button">Report Item</a>
            </div>
        </div>

        <div class="space-y-3 mt-5">
            @forelse ($items as $item)
                <article class="activity-row">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="chip chip-{{ $item->type }}">{{ strtoupper($item->type) }}</span>
                            <span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                        </div>
                        <h3 class="mt-2 font-bold text-[var(--heading)]">{{ $item->title }}</h3>
                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ str($item->category)->headline() }}</p>
                        <p class="text-sm text-slate-500">{{ $item->location }} | {{ $item->reported_at->format('M d, Y h:i A') }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $item->claims_count }} claim(s)</p>
                    </div>
                    <div class="action-stack">
                        <a href="{{ route('items.show', $item) }}" class="secondary-button">View</a>
                        <a href="{{ route('items.edit', $item) }}" class="primary-button">Edit</a>
                        @include('items._delete-button', [
                            'item' => $item,
                            'triggerClass' => 'danger-button',
                            'triggerText' => 'Delete',
                        ])
                    </div>
                </article>
            @empty
                <div class="empty-box">
                    <p class="empty-box-title">You have not created any private reports yet.</p>
                    <p class="empty-box-text">When you submit a lost or found item, it will appear here for editing and deletion.</p>
                    <div class="empty-box-actions">
                        <a href="{{ route('items.create') }}" class="primary-button">Create Report</a>
                    </div>
                </div>
            @endforelse
        </div>
    </section>

    <x-modal name="report-delete-confirmation" :show="false" maxWidth="md">
        <div class="delete-modal">
            <p class="delete-modal-kicker">Delete report</p>
            <h3 class="delete-modal-title">Are you sure you want to delete this report?</h3>
            <p class="delete-modal-text">
                This will delete <strong data-delete-modal-title>this report</strong> from your active workspace.
                Admins can still review it later if needed.
            </p>

            <div class="delete-modal-summary">
                <p><span>Report ID:</span> <span data-delete-modal-id>#</span></p>
                <p><span>Owner:</span> <span data-delete-modal-owner></span></p>
                <p><span>Status:</span> <span data-delete-modal-status></span></p>
            </div>

            <div class="delete-modal-actions">
                <button type="button" class="secondary-button" x-on:click="$dispatch('close-modal', 'report-delete-confirmation')">
                    Cancel
                </button>

                <form method="POST" data-delete-modal-form>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger-button">Delete Report</button>
                </form>
            </div>
        </div>
    </x-modal>
@endsection
