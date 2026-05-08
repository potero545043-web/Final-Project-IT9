@extends('layouts.app')

@section('content')
    <section class="console-panel">
        <div class="console-panel-header">
            <div>
                <p class="console-label">{{ auth()->user()->isAdmin() ? 'Review Alerts' : 'My Alerts' }}</p>
                <h2 class="console-title">{{ auth()->user()->isAdmin() ? 'Claim reviews and system updates' : 'Updates about my reports and claims' }}</h2>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="secondary-button">Mark all as read</button>
            </form>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.open', $notification) }}" class="block">
                    <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 transition-colors hover:bg-slate-100 cursor-pointer">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-extrabold text-[var(--heading)]">{{ $notification->data['title'] ?? 'Notification' }}</h3>
                                    @if (is_null($notification->read_at))
                                        <span class="badge">Unread</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-slate-600">{{ $notification->data['message'] ?? '' }}</p>
                            </div>

                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ $notification->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </article>
                </a>
            @empty
                <div class="empty-box">
                    <p class="empty-box-title">No alerts available.</p>
                    <p class="empty-box-text">Updates about reports, ownership requests, and review activity will appear here.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </section>
@endsection
