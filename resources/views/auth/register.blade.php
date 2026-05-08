@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-2xl rounded-[2rem] border border-white/60 bg-white/90 p-8 shadow-xl shadow-orange-100">
        <p class="badge">New Account</p>
        <h2 class="mt-4 text-3xl font-black text-[var(--heading)]">Create your Lost and Found account</h2>
        <p class="mt-2 text-sm text-slate-600">Registration is now handled by Laravel Breeze, with your project’s student fields preserved.</p>

        <form method="POST" action="{{ route('register') }}" class="mt-8 grid gap-4 md:grid-cols-2">
            @csrf
            <div class="md:col-span-2">
                <label class="label" for="name">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" class="field" required autofocus autocomplete="name">
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div>
                <label class="label" for="student_id">Student or employee ID</label>
                <input id="student_id" type="text" name="student_id" value="{{ old('student_id') }}" class="field" required>
                <x-input-error :messages="$errors->get('student_id')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div>
                <label class="label" for="phone">Phone number</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="field" required>
                <x-input-error :messages="$errors->get('phone')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div class="md:col-span-2">
                <label class="label" for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" required autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <div class="password-field-wrap">
                    <input id="password" type="password" name="password" class="field password-field-input" required autocomplete="new-password" data-password-input>
                    <button
                        type="button"
                        class="password-toggle-button"
                        aria-label="Show password"
                        data-password-toggle
                        data-show-label="Show password"
                        data-hide-label="Hide password"
                    >
                        <span data-password-toggle-icon>Show</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div>
                <label class="label" for="password_confirmation">Confirm password</label>
                <div class="password-field-wrap">
                    <input id="password_confirmation" type="password" name="password_confirmation" class="field password-field-input" required autocomplete="new-password" data-password-input>
                    <button
                        type="button"
                        class="password-toggle-button"
                        aria-label="Show password"
                        data-password-toggle
                        data-show-label="Show password"
                        data-hide-label="Hide password"
                    >
                        <span data-password-toggle-icon>Show</span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div class="md:col-span-2 flex items-center justify-between gap-4">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:underline">Already registered?</a>
                <button type="submit" class="primary-button">Create account</button>
            </div>
        </form>
    </section>
@endsection
