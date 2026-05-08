@extends('layouts.app')

@section('content')
    <section class="summary-strip summary-strip-top">
        <article class="summary-pill summary-pill-blue">
            <div>
                <p class="summary-pill-label">Lost Reports</p>
                <p class="summary-pill-text">Items marked as lost</p>
                <p class="summary-pill-value">{{ $stats['lost'] ?? 0 }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-green">
            <div>
                <p class="summary-pill-label">Found Reports</p>
                <p class="summary-pill-text">Items marked as found</p>
                <p class="summary-pill-value">{{ $stats['found'] ?? 0 }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-red">
            <div>
                <p class="summary-pill-label">Resolved Cases</p>
                <p class="summary-pill-text">Reports already completed</p>
                <p class="summary-pill-value">{{ $stats['resolved_cases'] }}</p>
            </div>
        </article>
        <article class="summary-pill summary-pill-gold">
            <div>
                <p class="summary-pill-label">Total Listed</p>
                <p class="summary-pill-text">Reports in the current list</p>
                <p class="summary-pill-value">{{ $stats['total_reports'] }}</p>
            </div>
        </article>
    </section>
@endsection
