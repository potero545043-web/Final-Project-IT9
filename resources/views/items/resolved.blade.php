@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top">
        <article class="summary-pill summary-pill-blue">
            <div>
                <p class="summary-pill-label">Resolved Reports</p>
                <p class="summary-pill-text">All reports that have already been marked resolved</p>
                <p class="summary-pill-value">{{ $stats['total'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <p class="summary-pill-label">Claim Approved</p>
                <p class="summary-pill-text">Resolved reports with an approved ownership outcome</p>
                <p class="summary-pill-value">{{ $stats['claimed'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <p class="summary-pill-label">Closed Without Claim</p>
                <p class="summary-pill-text">Resolved reports that do not have an approved claim</p>
                <p class="summary-pill-value">{{ $stats['unclaimed'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <p class="summary-pill-label">Filtered Results</p>
                <p class="summary-pill-text">Resolved reports matching your current archive filters</p>
                <p class="summary-pill-value">{{ $stats['filtered'] }}</p>
            </div>
        </article>
    </section>

    <section class="console-panel mt-6">
        <div class="console-panel-header">
            <div></div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('items.archived') }}" class="secondary-button">{{ auth()->user()->isAdmin() ? 'Deleted Reports' : 'My Deleted Reports' }}</a>
                <a href="{{ route('dashboard') }}" class="secondary-button">Back to Dashboard</a>
            </div>
        </div>

        <form method="GET" action="{{ route('items.resolved') }}" class="mt-4 flex flex-nowrap items-center justify-start gap-2 overflow-x-auto">
            <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="field !w-40 flex-none px-2.5 py-1.5 text-xs" placeholder="Search">
            <select id="sort" name="sort" class="field !w-28 flex-none px-2.5 py-1.5 text-xs">
                    <option value="resolved_desc" @selected(($filters['sort'] ?? 'resolved_desc') === 'resolved_desc')>Newest</option>
                    <option value="resolved_asc" @selected(($filters['sort'] ?? '') === 'resolved_asc')>Oldest</option>
                    <option value="id_desc" @selected(($filters['sort'] ?? '') === 'id_desc')>ID desc</option>
                    <option value="id_asc" @selected(($filters['sort'] ?? '') === 'id_asc')>ID asc</option>
            </select>
            <button type="submit" class="primary-button flex-none px-3 py-1.5 text-xs">Apply</button>
            <a href="{{ route('items.resolved') }}" class="secondary-button flex-none px-3 py-1.5 text-xs">Reset</a>
        </form>
    </section>

    <section class="console-panel mt-6">
        <div class="console-panel-header">
            <div>
                <p class="console-label">Archive Listing</p>
                <h2 class="console-title">Resolved report history</h2>
            </div>
            <p class="text-sm text-slate-500">Read-only access for admins and users</p>
        </div>

        <div class="console-table-wrap">
            <table class="console-table">
                <thead>
                    <tr>
                        <th>Report</th>
                        <th>Reporter</th>
                        <th>Claim Outcome</th>
                        <th>Resolved Date</th>
                        <th>Admin Notes</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <p class="table-title-cell">{{ $item->title }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">Report #{{ $item->id }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="chip chip-{{ $item->type }}">{{ strtoupper($item->type) }}</span>
                                    <span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span>
                                </div>
                            </td>
                            <td>
                                <p class="font-semibold text-[var(--heading)]">{{ $item->user->name }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ str($item->category)->headline() }}</p>
                            </td>
                            <td>
                                @if ($item->approvedClaim)
                                    <p class="font-semibold text-emerald-700">Claim approved</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $item->approvedClaim->claimant->name }}</p>
                                @else
                                    <p class="font-semibold text-slate-700">Resolved without approved claim</p>
                                    <p class="mt-1 text-sm text-slate-500">No ownership handoff recorded</p>
                                @endif
                            </td>
                            <td>
                                <p class="font-semibold text-[var(--heading)]">{{ $item->updated_at->format('M d, Y') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->updated_at->format('h:i A') }}</p>
                            </td>
                            <td>
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ $item->approvedClaim?->review_notes ?: 'No admin notes provided.' }}
                                </p>
                            </td>
                            <td>
                                <a href="{{ route('items.show', $item) }}" class="table-link">View Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-box">
                                    <p class="empty-box-title">No resolved reports found.</p>
                                    <p class="empty-box-text">Try changing your filters or come back once administrators have closed more reports.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="mt-5">
                {{ $items->links() }}
            </div>
        @endif
    </section>
@endsection
