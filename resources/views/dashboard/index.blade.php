@extends('layouts.app')

@section('content')
    <section class="dashboard-grid">
        <div class="dashboard-card dashboard-card-blue">
            <div class="dashboard-card-icon">FI</div>
            <h2 class="dashboard-card-title">Found Item Entry</h2>
            <p class="dashboard-card-text">Register and manage found item reports.</p>
            <a href="{{ route('items.create') }}" class="dashboard-card-link">Create report</a>
        </div>

        <div class="dashboard-card dashboard-card-red">
            <div class="dashboard-card-icon">AE</div>
            <h2 class="dashboard-card-title">All Entries</h2>
            <p class="dashboard-card-text">View the complete list of item records.</p>
            <a href="{{ route('home') }}" class="dashboard-card-link">Open entries</a>
        </div>

        <div class="dashboard-card dashboard-card-green">
            <div class="dashboard-card-icon">LI</div>
            <h2 class="dashboard-card-title">Lost Item Inquiry</h2>
            <p class="dashboard-card-text">Track lost item cases and follow-up requests.</p>
            <a href="{{ route('home', ['type' => 'lost']) }}" class="dashboard-card-link">Review lost cases</a>
        </div>

        <div class="dashboard-card dashboard-card-gold">
            <div class="dashboard-card-icon">MC</div>
            <h2 class="dashboard-card-title">Matching Desk</h2>
            <p class="dashboard-card-text">Compare found items with user claims.</p>
            <a href="#review-queue" class="dashboard-card-link">Open queue</a>
        </div>
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-[0.95fr_1.2fr]">
        <div class="space-y-6">
            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Quick Entry</p>
                        <h3 class="console-title">Common item categories</h3>
                    </div>
                    <a href="{{ route('items.create') }}" class="table-link">Select manually</a>
                </div>

                <div class="quick-entry-grid">
                    <div class="quick-entry-item"><span class="quick-entry-icon">WA</span><span>Wallet</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">BA</span><span>Bag</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">GL</span><span>Glasses</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">ID</span><span>ID Card</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">CL</span><span>Clothing</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">PH</span><span>Phone</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">KE</span><span>Keys</span></div>
                    <div class="quick-entry-item"><span class="quick-entry-icon">EL</span><span>Electronics</span></div>
                </div>
            </div>

            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Statistics</p>
                        <h3 class="console-title">Current case summary</h3>
                    </div>
                </div>

                <div class="stats-mini-grid">
                    <article class="stats-mini-card">
                        <p class="stats-mini-label">Total Reports</p>
                        <p class="stats-mini-value">{{ $stats['total_reports'] }}</p>
                    </article>
                    <article class="stats-mini-card">
                        <p class="stats-mini-label">Open Cases</p>
                        <p class="stats-mini-value">{{ $stats['open_cases'] }}</p>
                    </article>
                    <article class="stats-mini-card">
                        <p class="stats-mini-label">Resolved</p>
                        <p class="stats-mini-value">{{ $stats['resolved_cases'] }}</p>
                    </article>
                    <article class="stats-mini-card">
                        <p class="stats-mini-label">My Active</p>
                        <p class="stats-mini-value">{{ $stats['my_active_reports'] }}</p>
                    </article>
                    @if (auth()->user()->isAdmin())
                        <article class="stats-mini-card stats-mini-card-wide">
                            <p class="stats-mini-label">Pending Claims</p>
                            <p class="stats-mini-value">{{ $stats['pending_claims'] }}</p>
                        </article>
                    @endif
                </div>
            </div>

            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">My Reports</p>
                        <h3 class="console-title">Recent submissions</h3>
                    </div>
                    <a href="{{ route('items.create') }}" class="table-link">Add report</a>
                </div>

                <div class="space-y-3">
                    @forelse ($myItems->take(4) as $item)
                        <article class="activity-row">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="chip chip-{{ $item->type }}">{{ strtoupper($item->type) }}</span>
                                    <span class="chip chip-neutral">{{ str($item->status)->headline() }}</span>
                                </div>
                                <h4 class="mt-2 font-bold text-[var(--heading)]">{{ $item->title }}</h4>
                                <p class="text-sm text-slate-500">{{ $item->location }} | {{ $item->reported_at->format('M d, Y h:i A') }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('items.show', $item) }}" class="secondary-button">View</a>
                                <a href="{{ route('items.edit', $item) }}" class="secondary-button">Edit</a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-box">You have not submitted any reports yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">All Found Entries</p>
                        <h3 class="console-title">Latest item records</h3>
                    </div>
                    <a href="{{ route('home') }}" class="table-link">View full directory</a>
                </div>

                <div class="console-table-wrap">
                    <table class="console-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Category</th>
                                <th>Quick Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($myItems->concat($pendingReviews->pluck('item'))->unique('id')->take(6) as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ strtoupper($item->type) }}</td>
                                    <td>{{ str($item->status)->headline() }}</td>
                                    <td>{{ str($item->category)->headline() }}</td>
                                    <td><a href="{{ route('items.show', $item) }}" class="table-link">Open</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-slate-500">No records available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="console-panel" id="review-queue">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Inquiry</p>
                        <h3 class="console-title">Pending claim reviews</h3>
                    </div>
                </div>

                <div class="console-table-wrap">
                    <table class="console-table">
                        <thead>
                            <tr>
                                <th>Claimant</th>
                                <th>Item</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingReviews as $claim)
                                <tr>
                                    <td>{{ $claim->claimant->name }}</td>
                                    <td>{{ $claim->item->title }}</td>
                                    <td>{{ str($claim->status)->headline() }}</td>
                                    <td><a href="{{ route('items.show', $claim->item) }}" class="table-link">Review</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-500">No pending claims to review right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="console-panel">
                <div class="console-panel-header">
                    <div>
                        <p class="console-label">Claim Activity</p>
                        <h3 class="console-title">My submitted claims</h3>
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
                            <span class="chip chip-neutral">{{ str($claim->status)->headline() }}</span>
                        </article>
                    @empty
                        <div class="empty-box">You have not submitted a claim yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
