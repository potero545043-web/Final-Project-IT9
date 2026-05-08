@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top">
        <article class="summary-pill summary-pill-red">
            <div>
                <p class="summary-pill-label">{{ auth()->user()->isAdmin() ? 'Deleted Reports' : 'My Deleted Reports' }}</p>
                <p class="summary-pill-text">{{ auth()->user()->isAdmin() ? 'Reports hidden from normal users but kept for audit' : 'Reports you removed from your active list' }}</p>
                <p class="summary-pill-value">{{ $stats['total'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-blue">
            <div>
                <p class="summary-pill-label">Lost Reports</p>
                <p class="summary-pill-text">Archived lost item submissions</p>
                <p class="summary-pill-value">{{ $stats['lost'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <p class="summary-pill-label">Found Reports</p>
                <p class="summary-pill-text">Archived found item submissions</p>
                <p class="summary-pill-value">{{ $stats['found'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <p class="summary-pill-label">Filtered Results</p>
                <p class="summary-pill-text">Deleted reports matching your current filters</p>
                <p class="summary-pill-value">{{ $stats['filtered'] }}</p>
            </div>
        </article>
    </section>

    <section class="console-panel mt-6">
        <div class="console-panel-header">
            <div>
                <p class="console-label">Archive Listing</p>
                <h2 class="console-title">{{ auth()->user()->isAdmin() ? 'Deleted reports' : 'My deleted reports' }}</h2>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('items.mine') }}" class="secondary-button">Back to My Reports</a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('dashboard') }}" class="secondary-button">Admin Dashboard</a>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('items.archived') }}" class="mt-4 flex flex-nowrap items-center justify-start gap-2 overflow-x-auto">
            <input id="q" type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="field !w-40 flex-none px-2.5 py-1.5 text-xs" placeholder="Search">
            <select id="sort" name="sort" class="field !w-28 flex-none px-2.5 py-1.5 text-xs">
                <option value="deleted_desc" @selected(($filters['sort'] ?? 'deleted_desc') === 'deleted_desc')>Newest</option>
                <option value="deleted_asc" @selected(($filters['sort'] ?? '') === 'deleted_asc')>Oldest</option>
                <option value="id_desc" @selected(($filters['sort'] ?? '') === 'id_desc')>ID desc</option>
                <option value="id_asc" @selected(($filters['sort'] ?? '') === 'id_asc')>ID asc</option>
            </select>
            <button type="submit" class="primary-button flex-none px-3 py-1.5 text-xs">Apply</button>
            <a href="{{ route('items.archived') }}" class="secondary-button flex-none px-3 py-1.5 text-xs">Reset</a>
        </form>
    </section>

    <section class="console-panel mt-6">
        <div class="console-table-wrap">
            <table class="console-table">
                <thead>
                    <tr>
                        <th>Report</th>
                        <th>Reporter</th>
                        <th>Archived By</th>
                        <th>Archived Date</th>
                        <th>Status Before Archive</th>
                        <th>Actions</th>
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
                                <p class="font-semibold text-[var(--heading)]">{{ $item->deletedBy?->name ?? 'Unknown user' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->user_id === $item->deleted_by ? 'Owner action' : 'Admin action' }}</p>
                            </td>
                            <td>
                                <p class="font-semibold text-[var(--heading)]">{{ optional($item->deleted_at)->format('M d, Y') ?? 'Unknown' }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ optional($item->deleted_at)->format('h:i A') ?? '' }}</p>
                            </td>
                            <td>
                                <p class="text-sm leading-6 text-slate-600">
                                    {{ $item->archived_from_status ? \App\Models\Item::statusLabelFor($item->archived_from_status) : 'Unknown' }}
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
                                    <p class="empty-box-title">No deleted reports found.</p>
                                    <p class="empty-box-text">{{ auth()->user()->isAdmin() ? 'Deleted reports will appear here for audit and review.' : 'Reports you delete will appear here for your reference.' }}</p>
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
