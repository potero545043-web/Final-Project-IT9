@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <div class="panel">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="badge">Submission complete</p>
                    <h2 class="mt-4 text-3xl font-black text-[var(--heading)]">Your report was submitted successfully</h2>
                    <p class="mt-2 text-sm text-slate-600">Keep the report ID for tracking and future follow-up.</p>
                </div>
                <div class="confirmation-id-card">
                    <p class="confirmation-id-label">Report ID</p>
                    <p class="confirmation-id-value">#{{ str_pad((string) $item->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="panel">
                <p class="section-kicker">Report summary</p>
                <h3 class="section-title">Overview of your submitted report</h3>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <article class="item-meta-card">
                        <p class="item-meta-label">Item title</p>
                        <p class="item-meta-value">{{ $item->title }}</p>
                    </article>
                    <article class="item-meta-card">
                        <p class="item-meta-label">Report type</p>
                        <p class="item-meta-value">{{ strtoupper($item->type) }}</p>
                    </article>
                    <article class="item-meta-card">
                        <p class="item-meta-label">Category</p>
                        <p class="item-meta-value">{{ str($item->category)->headline() }}</p>
                    </article>
                    <article class="item-meta-card">
                        <p class="item-meta-label">Status</p>
                        <p class="item-meta-value">{{ $item->status_label }}</p>
                    </article>
                    <article class="item-meta-card">
                        <p class="item-meta-label">Location</p>
                        <p class="item-meta-value">{{ $item->location }}</p>
                    </article>
                    <article class="item-meta-card">
                        <p class="item-meta-label">Date reported</p>
                        <p class="item-meta-value">{{ $item->reported_at->format('M d, Y h:i A') }}</p>
                    </article>
                </div>

                <div class="mt-6 rounded-[1.5rem] bg-slate-50 p-5">
                    <p class="item-meta-label">Description</p>
                    <p class="mt-3 text-sm leading-7 text-slate-700">{{ $item->description }}</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="panel">
                    @if ($item->photo_src)
                        <img src="{{ $item->photo_src }}" alt="{{ $item->title }}" class="item-hero-image">
                    @else
                        <div class="item-image-placeholder">
                            <p class="text-sm font-semibold text-slate-500">No item photo was uploaded for this report.</p>
                        </div>
                    @endif
                </div>

                <div class="panel">
                    <p class="section-kicker">What happens next?</p>
                    <h3 class="section-title">Next steps</h3>
                    <div class="mt-4 space-y-3 text-sm leading-7 text-slate-600">
                        <p>1. Your report is now saved in the system and can be tracked by report ID.</p>
                        <p>2. You can review or update the report details at any time from your dashboard.</p>
                        <p>3. If someone submits an ownership request, you will receive an alert.</p>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('items.show', $item) }}" class="primary-button">View full report</a>
                        <a href="{{ route('dashboard') }}" class="secondary-button">Go to dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
