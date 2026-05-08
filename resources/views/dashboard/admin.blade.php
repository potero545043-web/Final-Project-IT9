@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top">
        <article class="summary-pill summary-pill-blue">
            <div>
                <div>
                    <p class="summary-pill-label">Under Review</p>
                    <p class="summary-pill-text">Reports waiting for admin approval</p>
                </div>
                <p class="summary-pill-value">{{ $stats['under_review'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <div>
                    <p class="summary-pill-label">Pending Claims</p>
                    <p class="summary-pill-text">Claims waiting for review</p>
                </div>
                <p class="summary-pill-value">{{ $stats['pending_claims'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <div>
                    <p class="summary-pill-label">Open Cases</p>
                    <p class="summary-pill-text">Approved reports now visible for claims</p>
                </div>
                <p class="summary-pill-value">{{ $stats['open_cases'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <div>
                    <p class="summary-pill-label">Resolved Cases</p>
                    <p class="summary-pill-text">Reports already completed and closed</p>
                </div>
                <p class="summary-pill-value">{{ $stats['resolved_cases'] }}</p>
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-6">
            <div class="console-panel" id="pending-claims">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Priority Queue</p>
                        <h3 class="console-title">Pending claims</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($pendingReviews as $claim)
                        <article class="activity-row">
                            <div>
                                <span class="chip {{ $claim->status_badge_class }}">{{ $claim->status_label }}</span>
                                <h4 class="mt-2 font-bold text-[var(--heading)]">{{ $claim->item->title }}</h4>
                                <p class="text-sm text-slate-500">Claimant: {{ $claim->claimant->name }} <span class="role-badge role-badge-{{ $claim->claimant->role }}">{{ ucfirst($claim->claimant->role) }}</span></p>
                                <p class="mt-2 text-sm text-slate-600">{{ $claim->proof_details }}</p>
                                @if ($claim->finder_feedback_label)
                                    <p class="mt-2 text-sm text-slate-500">
                                        Finder input:
                                        <span class="chip {{ $claim->finder_feedback_badge_class }}">{{ $claim->finder_feedback_label }}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="action-stack">
                                <a href="{{ route('items.show', $claim->item) }}" class="secondary-button">Review claim</a>
                                <form method="POST" action="{{ route('claims.update', $claim) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="primary-button">Approve Claim</button>
                                </form>
                                <form method="POST" action="{{ route('claims.update', $claim) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="danger-button">Reject Claim</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">No pending claim reviews right now.</p>
                            <p class="empty-box-text">New ownership requests will appear here when students submit them.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="console-panel" id="items-needing-review">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Review Queue</p>
                        <h3 class="console-title">Items needing review</h3>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($itemsNeedingReview as $item)
                        <article class="activity-row">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="chip chip-{{ $item->type }}">{{ strtoupper($item->type) }}</span>
                                    <span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                </div>
                                <h4 class="mt-2 font-bold text-[var(--heading)]">{{ $item->title }}</h4>
                                <p class="text-sm text-slate-500">Reporter: {{ $item->user->name }} <span class="role-badge role-badge-{{ $item->user->role }}">{{ ucfirst($item->user->role) }}</span> | {{ $item->claims_count }} claim(s)</p>
                            </div>
                            <div class="action-stack">
                                <form method="POST" action="{{ route('items.status', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="open">
                                    <button type="submit" class="primary-button">Approve Report</button>
                                </form>
                                <a href="{{ route('items.show', $item) }}" class="secondary-button">Open report</a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">No items are waiting for review right now.</p>
                            <p class="empty-box-text">Only reports that are still under review will appear here for admin approval.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="console-panel" id="recent-reports">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Case Monitoring</p>
                        <h3 class="console-title">Recently submitted reports</h3>
                    </div>
                </div>

                <div class="console-table-wrap">
                    <table class="console-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Reporter</th>
                                <th>Status</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentItems as $item)
                                <tr>
                                    <td class="table-title-cell">{{ $item->title }}</td>
                                    <td>{{ $item->user->name }} <span class="role-badge role-badge-{{ $item->user->role }}">{{ ucfirst($item->user->role) }}</span></td>
                                    <td><span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                                    <td><a href="{{ route('items.show', $item) }}" class="table-link">Open</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-500">No recently submitted reports yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="console-panel" id="close-to-resolution">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Closeout Queue</p>
                        <h3 class="console-title">Cases close to resolution</h3>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('items.resolved') }}" class="table-link">Resolved Reports</a>
                        <a href="{{ route('items.archived') }}" class="table-link">Deleted Reports</a>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse ($casesCloseToResolution as $item)
                        <article class="activity-row">
                            <div>
                                <span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                <h4 class="mt-2 font-bold text-[var(--heading)]">{{ $item->title }}</h4>
                                <p class="text-sm text-slate-500">Reporter: {{ $item->user->name }} <span class="role-badge role-badge-{{ $item->user->role }}">{{ ucfirst($item->user->role) }}</span> | {{ $item->claims_count }} claim(s)</p>
                            </div>
                            <div class="action-stack">
                                <form method="POST" action="{{ route('items.status', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="resolved">
                                    <button type="submit" class="primary-button">Mark as Resolved</button>
                                </form>
                                <a href="{{ route('items.show', $item) }}" class="secondary-button">Open case</a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-box">
                            <p class="empty-box-title">No cases are close to resolution right now.</p>
                            <p class="empty-box-text">Cases that are nearly completed will appear here for faster follow-up.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Claims</p>
                        <h3 class="console-title">Recent claim activity</h3>
                    </div>
                </div>

                <div class="console-table-wrap">
                    <table class="console-table">
                        <thead>
                            <tr>
                                <th>Claimant</th>
                                <th>Item</th>
                                <th>Status</th>
                                <th>Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentClaims as $claim)
                                <tr>
                                    <td class="table-title-cell">{{ $claim->claimant->name }}</td>
                                    <td>{{ $claim->item->title }}</td>
                                    <td><span class="chip {{ $claim->status_badge_class }}">{{ $claim->status_label }}</span></td>
                                    <td><a href="{{ route('items.show', $claim->item) }}" class="table-link">Open</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-500">No recent claim activity yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Fraud Detection</p>
                        <h3 class="console-title">Flags to monitor</h3>
                    </div>
                </div>

                <div class="space-y-3 text-sm text-slate-600">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">Check claims with repeated submissions from the same user.</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">Review mismatched proof details and suspicious duplicate ownership attempts.</div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">Focus on unresolved items that have multiple pending claims.</div>
                </div>
            </div>
        </div>
    </section>
@endsection
