@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top">
        <article class="summary-pill summary-pill-blue">
            <div>
                <div>
                    <p class="summary-pill-label">Lost Reports</p>
                    <p class="summary-pill-text">Items marked as lost</p>
                </div>
                <p class="summary-pill-value">{{ $stats['lost'] ?? 0 }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <div>
                    <p class="summary-pill-label">Found Reports</p>
                    <p class="summary-pill-text">Items marked as found</p>
                </div>
                <p class="summary-pill-value">{{ $stats['found'] ?? 0 }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <div>
                    <p class="summary-pill-label">Resolved Cases</p>
                    <p class="summary-pill-text">Reports already completed</p>
                </div>
                <p class="summary-pill-value">{{ $stats['resolved'] ?? 0 }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <div>
                    <p class="summary-pill-label">Total Listed</p>
                    <p class="summary-pill-text">Reports in the current list</p>
                </div>
                <p class="summary-pill-value">{{ $items->total() }}</p>
            </div>
        </article>
    </section>

    <section class="console-panel">
        <div class="console-panel-header">
            <div>
                <p class="console-label">Item Directory</p>
                <h2 class="console-title">Browse lost and found items</h2>
            </div>
            @auth
                <a href="{{ route('items.create') }}" class="primary-button">Report Item</a>
            @endauth
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-[1.8fr_1fr_1fr_1fr_1fr]">
            <input type="text" form="filters" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search item, place, or description" class="field lg:col-span-1">

            <select form="filters" name="type" class="field">
                <option value="">All types</option>
                <option value="lost" @selected(($filters['type'] ?? '') === 'lost')>Lost</option>
                <option value="found" @selected(($filters['type'] ?? '') === 'found')>Found</option>
            </select>

            <select form="filters" name="status" class="field">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ \App\Models\Item::statusLabelFor($status) }}</option>
                @endforeach
            </select>

            <select form="filters" name="category" class="field">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ str($category)->headline() }}</option>
                @endforeach
            </select>

            <div class="flex gap-3">
                <button form="filters" type="submit" class="primary-button">Search</button>
                <a href="{{ route('home') }}" class="secondary-button">Reset</a>
            </div>
        </div>

        <form id="filters" method="GET" action="{{ route('home') }}"></form>
    </section>

    <section class="console-panel mt-6">
        <div class="console-panel-header">
            <div>
                <p class="console-label">Entries Table</p>
                <h3 class="console-title">Current reports</h3>
            </div>
            <p class="text-sm text-slate-500">{{ $items->total() }} records</p>
        </div>

        <div class="console-table-wrap">
            <table class="console-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Reported</th>
                        <th>View</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td class="table-title-cell">{{ $item->title }}</td>
                            <td>{{ strtoupper($item->type) }}</td>
                            <td><span class="chip {{ $item->status_badge_class }}">{{ $item->status_label }}</span></td>
                            <td>{{ str($item->category)->headline() }}</td>
                            <td>{{ $item->location }}</td>
                            <td>{{ $item->reported_at->format('M d, Y') }}</td>
                            <td><a href="{{ route('items.show', $item) }}" class="table-link">Open</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-500">
                                <div class="py-6">
                                    <p class="empty-box-title">No items match your current filters.</p>
                                    <p class="empty-box-text">Try changing your search filters or report a new item if it is not listed yet.</p>
                                    @auth
                                        <div class="empty-box-actions justify-center">
                                            <a href="{{ route('items.create') }}" class="primary-button">Report Item</a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </section>
@endsection
