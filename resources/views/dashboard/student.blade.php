@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top">
        <article class="summary-pill summary-pill-blue">
            <div>
                <div>
                    <p class="summary-pill-label">My Reports</p>
                    <p class="summary-pill-text">Items you reported to the system</p>
                </div>
                <p class="summary-pill-value">{{ $myItems->count() }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <div>
                    <p class="summary-pill-label">My Claims</p>
                    <p class="summary-pill-text">Ownership requests you submitted</p>
                </div>
                <p class="summary-pill-value">{{ $myClaims->count() }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <div>
                    <p class="summary-pill-label">Active Reports</p>
                    <p class="summary-pill-text">Reports that still need follow-up</p>
                </div>
                <p class="summary-pill-value">{{ $stats['my_active_reports'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <div>
                    <p class="summary-pill-label">Unread Alerts</p>
                    <p class="summary-pill-text">New updates waiting for your review</p>
                </div>
                <p class="summary-pill-value">{{ $notificationSummary['unread'] }}</p>
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1fr_0.95fr]">
        <div class="space-y-6">
            <div class="console-panel" id="my-reports">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Personal Reports</p>
                        <h3 class="console-title">My reports</h3>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('items.resolved') }}" class="table-link">Resolved Reports</a>
                        <a href="{{ route('items.create') }}" class="table-link">Add report</a>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($myItems as $item)
                        <article class="activity-row">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="chip chip-{{ $item->type }}">{{ strtoupper($item->type) }}</span>
                                    <span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                </div>
                                <h4 class="mt-2 font-bold text-[var(--heading)]">{{ $item->title }}</h4>
                                <p class="text-sm text-slate-500">{{ $item->location }} | {{ $item->reported_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('items.show', $item) }}" class="secondary-button">View</a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">You have not submitted any reports yet.</p>
                            <p class="empty-box-text">Start with a lost item report or a found item report so others can help identify the item.</p>
                            <div class="empty-box-actions">
                                <a href="{{ route('items.create', ['type' => 'lost']) }}" class="primary-button">Report Lost Item</a>
                                <a href="{{ route('items.create', ['type' => 'found']) }}" class="secondary-button">Report Found Item</a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="console-panel" id="my-claims">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Claim Tracking</p>
                        <h3 class="console-title">My claims</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($myClaims as $claim)
                        <article class="activity-row">
                            <div>
                                <h4 class="font-bold text-[var(--heading)]">{{ $claim->item->title }}</h4>
                                <p class="text-sm text-slate-500">Owner: {{ $claim->item->user->name }}</p>
                                <p class="mt-2 text-sm text-slate-600">{{ $claim->message }}</p>
                            </div>
                            <span class="chip {{ $claim->status_badge_class }}">{{ $claim->status_label }}</span>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">You have not filed any ownership requests yet.</p>
                            <p class="empty-box-text">Browse reported items first, then send an ownership request when you find something that matches.</p>
                            <div class="empty-box-actions">
                                <a href="{{ route('home') }}" class="primary-button">Browse Items</a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Status Updates</p>
                        <h3 class="console-title">Recent status updates</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($recentStatusUpdates as $update)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="font-bold text-[var(--heading)]">{{ $update['title'] }}</h4>
                                    <p class="mt-1 text-sm text-slate-600">{{ $update['message'] }}</p>
                                </div>
                                <span class="chip chip-neutral">{{ $update['status'] }}</span>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $update['timestamp']->diffForHumans() }}</p>
                                <a href="{{ $update['link'] }}" class="table-link">View case</a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">No recent status updates yet.</p>
                            <p class="empty-box-text">Updates about your reports and ownership requests will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Notifications</p>
                        <h3 class="console-title">Recent alerts</h3>
                    </div>
                    <a href="{{ route('notifications.index') }}" class="table-link">View all</a>
                </div>

                <div class="space-y-3">
                    @forelse ($notificationSummary['recent'] as $notification)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="font-bold text-[var(--heading)]">{{ $notification->data['title'] ?? 'Notification' }}</h4>
                                    <p class="mt-1 text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                                </div>
                                @if (is_null($notification->read_at))
                                    <span class="badge">Unread</span>
                                @endif
                            </div>
                            <p class="mt-3 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">No alerts yet.</p>
                            <p class="empty-box-text">When something changes in your reports or ownership requests, you will see it here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
