@extends('layouts.app')

@section('content')
    <section class="panel mx-auto max-w-4xl">
        <p class="badge">Update report</p>
        <h2 class="mt-4 text-3xl font-black text-[var(--heading)]">Edit item details</h2>

        <form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data" class="mt-8">
            @csrf
            @method('PUT')
            @include('items._form')
        </form>
    </section>
@endsection
