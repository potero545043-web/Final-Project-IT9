@extends('layouts.app')

@section('content')
    <section class="item-hero-grid">
        <div class="panel">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="chip chip-{{ $item->type }}">{{ strtoupper($item->type) }}</span>
                        <span class="chip chip-neutral">{{ $item->status_label }}</span>
                    </div>
                    <h2 class="mt-4 text-3xl font-black text-[var(--heading)]">{{ $item->title }}</h2>
                    <p class="mt-3 text-sm text-slate-500">
                        {{ str($item->category)->headline() }} • Reported by {{ $item->user->name }}
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <article class="item-meta-card">
                    <p class="item-meta-label">Location</p>
                    <p class="item-meta-value">{{ $item->location }}</p>
                </article>
                <article class="item-meta-card">
                    <p class="item-meta-label">Date reported</p>
                    <p class="item-meta-value">{{ $item->reported_at->format('M d, Y h:i A') }}</p>
                </article>
                <article class="item-meta-card">
                    <p class="item-meta-label">Status</p>
                    <p class="item-meta-value">{{ $item->status_label }}</p>
                </article>
            </div>
        </div>

        <div class="panel">
            @if ($item->photo_src)
                <img src="{{ $item->photo_src }}" alt="{{ $item->title }}" class="item-hero-image">
            @else
                <div class="item-image-placeholder">
                    <p class="text-sm font-semibold text-slate-500">No item photo uploaded yet.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="mt-8 grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="space-y-8">
            @if ($item->status === 'resolved')
                <div class="panel rounded-[1.5rem] bg-emerald-50 border border-emerald-200">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-emerald-900">This case has been resolved</h3>
                            <p class="text-sm text-emerald-700">
                                @if ($item->resolved_at)
                                    Resolved on {{ $item->resolved_at->format('M d, Y \a\t h:i A') }}
                                    @if ($item->resolvedBy)
                                        by {{ $item->resolvedBy->name }}
                                    @endif
                                @else
                                    This case has been marked as resolved.
                                @endif
                            </p>
                            @if ($item->resolution_type)
                                <p class="mt-2 text-xs font-semibold text-emerald-600 uppercase tracking-[0.1em]">
                                    {{ match($item->resolution_type) {
                                        'returned_to_owner' => '✓ Item Returned to Owner',
                                        'unclaimed_closed' => '📋 Closed Without Claim',
                                        'invalid_report' => '⚠ Invalid Report',
                                        default => str($item->resolution_type)->headline(),
                                    } }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="panel">
                <p class="section-kicker">Item details</p>
                <h3 class="section-title">Description and contact details</h3>
                <p class="mt-4 text-base leading-8 text-slate-700">{{ $item->description }}</p>

                <div class="mt-8 grid gap-4 md:grid-cols-2">
                    @if ($item->type === 'lost')
                        <article class="rounded-[1.5rem] bg-slate-50 p-5">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Reward</p>
                            <p class="mt-2 text-lg font-semibold text-slate-800">
                                {{ $item->reward_amount ? 'PHP '.number_format((float) $item->reward_amount, 2) : 'No reward indicated' }}
                            </p>
                        </article>
                    @endif
                    <article class="rounded-[1.5rem] bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Contact person</p>
                        <p class="mt-2 text-lg font-semibold text-slate-800">{{ $item->contact_name }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $item->contact_email }}</p>
                    </article>
                    <article class="rounded-[1.5rem] bg-slate-50 p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Contact phone</p>
                        <p class="mt-2 text-lg font-semibold text-slate-800">{{ $item->contact_phone ?: 'Not provided' }}</p>
                    </article>
                </div>
            </div>

            <div class="panel">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="section-kicker">Case progress</p>
                        <h3 class="section-title">Status history</h3>
                    </div>
                    <span class="chip chip-neutral">{{ $item->status_label }}</span>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($item->statusTimeline() as $event)
                        <article class="status-history-row">
                            <div class="status-history-marker"></div>
                            <div>
                                <h4 class="font-bold text-[var(--heading)]">{{ $event['title'] }}</h4>
                                <p class="mt-1 text-sm text-slate-600">{{ $event['description'] }}</p>
                                <p class="mt-2 text-xs uppercase tracking-[0.2em] text-slate-400">{{ $event['timestamp']->format('M d, Y h:i A') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                @auth
                    @if (auth()->user()->isAdmin())
                        <div class="mt-6 flex flex-wrap justify-end gap-3">
                            @if ($item->status === 'under_review')
                                <form method="POST" action="{{ route('items.status', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="open">
                                    <button type="submit" class="primary-button text-xs px-3 py-1.5">Approve Report</button>
                                </form>
                            @endif
                            @if (in_array($item->status, ['open', 'claimed'], true))
                                <form
                                    method="POST"
                                    action="{{ route('items.status', $item) }}"
                                    onsubmit="return confirm('Are you sure you want to mark this as resolved?');"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="resolved">
                                    <button type="submit" class="resolved-action-button text-xs px-3 py-1.5">Mark Resolved</button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        </div>

        <div class="space-y-8">
            @if (! in_array($item->status, ['resolved', 'deleted'], true))
                @auth
                    @if (auth()->id() === $item->user_id)
                        <div class="panel">
                            <p class="badge">Report owner</p>
                            <h3 class="mt-4 text-2xl font-black text-[var(--heading)]">Ownership requests for your item</h3>
                            <p class="mt-2 text-sm text-slate-600">You posted this item, so claim requests will appear below for your review.</p>
                        </div>
                        @elseif ($viewerClaim)
                        <div class="panel">
                            <p class="badge">{{ $item->type === 'found' ? 'My ownership request' : 'My finder response' }}</p>
                            <h3 class="mt-4 text-2xl font-black text-[var(--heading)]">{{ $viewerClaim->status_label }}</h3>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ $item->type === 'found'
                                    ? 'You already submitted a request for this item. Check the current status and review notes below.'
                                    : 'You already submitted a finder response for this lost item. Check the current status and review notes below.' }}
                            </p>
                            <div class="mt-5 rounded-[1.5rem] bg-slate-50 p-5">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ $item->type === 'found' ? 'Your message' : 'Your finder details' }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $viewerClaim->message }}</p>
                                <article class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Contact phone</p>
                                    <p class="mt-2 text-sm font-semibold text-slate-800">{{ $viewerClaim->contact_phone ?: ($viewerClaim->claimant->phone ?: 'Not provided') }}</p>
                                </article>
                                @if ($viewerClaim->review_notes)
                                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Review notes</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $viewerClaim->review_notes }}</p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="panel">
                            <p class="badge">{{ $item->type === 'found' ? 'Ownership request' : 'Finder response' }}</p>
                            <h3 class="mt-4 text-2xl font-black text-[var(--heading)]">
                                {{ $item->type === 'found' ? 'Is this your item?' : 'Have you found this item?' }}
                            </h3>
                            <p class="mt-2 text-sm text-slate-600">
                                {{ $item->type === 'found'
                                    ? 'Use the form below if you can prove this item belongs to you.'
                                    : 'Use the form below to tell the report owner that you found this item and share any supporting details or evidence.' }}
                        </p>
                        <form method="POST" action="{{ route('claims.store', $item) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label class="label">{{ $item->type === 'found' ? 'Why this item is yours' : 'Where and how you found it' }}</label>
                                <textarea name="message" rows="4" class="field" placeholder="{{ $item->type === 'found' ? 'Explain why you believe this item belongs to you.' : 'Explain where you found the item, when you found it, and how the owner can verify it.' }}" required>{{ old('message') }}</textarea>
                            </div>
                            <div>
                                <label class="label">{{ $item->type === 'found' ? 'Ownership proof details' : 'Supporting details' }}</label>
                                <textarea name="proof_details" rows="4" class="field" placeholder="{{ $item->type === 'found' ? 'Describe unique marks, contents, serial number, color, or any identifying proof.' : 'Describe the item condition, exact place found, packaging, surroundings, or any details that support your response.' }}" required>{{ old('proof_details') }}</textarea>
                            </div>
                            <div>
                                <label class="label">Your contact phone (optional)</label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', auth()->user()->phone) }}" class="field">
                            </div>
                            <div>
                                <label class="label">{{ $item->type === 'found' ? 'Proof image' : 'Evidence image' }}</label>
                                <input type="file" name="proof_image" accept=".jpg,.jpeg,.png,.webp,image/*" class="field file:mr-4 file:rounded-lg file:border-0 file:bg-blue-100 file:px-4 file:py-2 file:font-semibold file:text-blue-700" data-image-input data-preview-target="proof-photo-preview" data-preview-label="Proof image preview">
                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $item->type === 'found'
                                        ? 'Upload a photo that supports your ownership request. Accepted files: JPG, PNG, WEBP. Maximum size: 3MB.'
                                        : 'Upload a photo that shows the item or where it was found. Accepted files: JPG, PNG, WEBP. Maximum size: 3MB.' }}
                                </p>
                                <div class="image-preview-card mt-4" id="proof-photo-preview" data-preview-card hidden>
                                    <p class="image-preview-label">{{ $item->type === 'found' ? 'Proof image preview' : 'Evidence image preview' }}</p>
                                    <img src="" alt="Proof image preview" class="image-preview-frame" data-preview-image hidden>
                                    <p class="image-preview-empty" data-preview-empty>Select an image to preview it before submitting.</p>
                                </div>
                            </div>
                            <button type="submit" class="primary-button">{{ $item->type === 'found' ? 'Submit ownership request' : 'Submit finder response' }}</button>
                        </form>
                    </div>
                    @endif
                @endauth
            @endif
        </div>
    </section>

    <section class="mt-8">
        <div class="panel">
            @guest
                @if ($item->type === 'found')
                    <p class="badge">Need an account?</p>
                    <h3 class="mt-4 text-2xl font-black text-[var(--heading)]">Sign in before sending an ownership request</h3>
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('login') }}" class="primary-button">Login</a>
                        <a href="{{ route('register') }}" class="secondary-button">Register</a>
                    </div>
                @else
                    <p class="badge">Need an account?</p>
                    <h3 class="mt-4 text-2xl font-black text-[var(--heading)]">Sign in before sending a finder response</h3>
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('login') }}" class="primary-button">Login</a>
                        <a href="{{ route('register') }}" class="secondary-button">Register</a>
                    </div>
                @endif
            @endguest
        </div>

        <div class="panel">
                <h3 class="text-2xl font-black text-[var(--heading)]">
                    {{ $item->type === 'found'
                        ? (auth()->check() && auth()->user()->isAdmin() ? 'Ownership requests to review' : 'Ownership request activity')
                        : 'Finder response activity' }}
                </h3>
                <div class="mt-5 space-y-4">
                    @forelse ($item->claims as $claim)
                        <article class="rounded-[1.5rem] border border-slate-200 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-black text-[var(--heading)]">{{ $claim->claimant->name }}</h4>
                                    <p class="mt-1 text-sm text-slate-500">{{ $claim->created_at->format('M d, Y h:i A') }} <span class="role-badge role-badge-{{ $claim->claimant->role }}">{{ ucfirst($claim->claimant->role) }}</span></p>
                                </div>
                                <span class="chip {{ $claim->status_badge_class }}">{{ $claim->status_label }}</span>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-slate-600">{{ $claim->message }}</p>
                            <p class="mt-3 text-sm text-slate-500">Proof details: {{ $claim->proof_details }}</p>
                            <article class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Contact phone</p>
                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $claim->contact_phone ?: ($claim->claimant->phone ?: 'Not provided') }}</p>
                            </article>
                            @if ($claim->proof_image_src)
                                <div class="mt-4">
                                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Proof image</p>
                                    <img src="{{ $claim->proof_image_src }}" alt="Proof image for {{ $claim->claimant->name }}" class="w-full h-auto max-h-96 rounded-[1.5rem] border border-slate-200 object-contain">
                                </div>
                            @endif
                            @if ($claim->review_notes)
                                <p class="mt-3 text-sm text-slate-500">Review notes: {{ $claim->review_notes }}</p>
                            @endif
                            @if ($claim->finder_feedback_label)
                                <article class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Finder input</p>
                                        <span class="chip {{ $claim->finder_feedback_badge_class }}">{{ $claim->finder_feedback_label }}</span>
                                    </div>
                                    @if ($claim->finder_notes)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $claim->finder_notes }}</p>
                                    @endif
                                </article>
                            @endif
                            @if (! in_array($item->status, ['resolved', 'deleted'], true))
                                @auth
                                    @if (auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('claims.update', $claim) }}" class="mt-4 space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label class="label" for="review_notes_{{ $claim->id }}">Review notes</label>
                                                <textarea id="review_notes_{{ $claim->id }}" name="review_notes" rows="3" class="field" placeholder="Add notes for the claimant.">{{ old('review_notes', $claim->review_notes) }}</textarea>
                                            </div>
                                            <div class="flex flex-wrap gap-3">
                                                <button type="submit" name="status" value="approved" class="primary-button">Approve Claim</button>
                                                <button type="submit" name="status" value="rejected" class="danger-button">Reject Claim</button>
                                            </div>
                                        </form>
                                    @elseif (auth()->id() === $item->user_id && $claim->status === 'pending')
                                        <form method="POST" action="{{ route('claims.update', $claim) }}" class="mt-4 space-y-3">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label class="label" for="finder_notes_{{ $claim->id }}">Finder notes</label>
                                                <textarea id="finder_notes_{{ $claim->id }}" name="finder_notes" rows="3" class="field" placeholder="Add notes for the admin.">{{ old('finder_notes', $claim->finder_notes) }}</textarea>
                                            </div>
                                            <div class="flex flex-wrap gap-3">
                                                <button type="submit" name="finder_feedback" value="confirmed" class="primary-button">Confirm Match</button>
                                                <button type="submit" name="finder_feedback" value="doubted" class="danger-button">Doubt Claim</button>
                                            </div>
                                        </form>
                                    @endif
                                @endauth
                            @endif
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-slate-300 p-8 text-center text-slate-500">
                            {{ $item->type === 'found' ? 'No ownership requests yet.' : 'No finder responses yet.' }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
