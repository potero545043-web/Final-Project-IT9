@extends('layouts.app')

@section('content')
    <section class="grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
        <div class="panel">
            <p class="section-kicker">Item Reporting Form</p>
            <h2 class="section-title">Create a lost or found item report</h2>
            <p class="mt-2 text-sm text-slate-600">Complete the form carefully so users and administrators can identify the item correctly.</p>

            <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" class="mt-8">
                @csrf
                @include('items._form')
            </form>
        </div>

        <aside class="panel">
            <p class="section-kicker">Before You Submit</p>
            <h3 class="section-title">Reporting checklist</h3>
            <div class="mt-5 space-y-4 text-sm leading-7 text-slate-600">
                <p>1. Select whether the item is lost or found.</p>
                <p>2. Enter a clear title such as "Black wallet near library".</p>
                <p>3. Add exact location, date, and identifying details.</p>
                <p>4. Include contact information for follow-up.</p>
                <p>5. Set the correct case status before saving.</p>
            </div>
        </aside>
    </section>
@endsection
