@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl rounded-[2rem] border border-white/60 bg-white/90 p-8 shadow-xl shadow-orange-100">
        <p class="badge">Secure Access</p>
        <h2 class="mt-4 text-3xl font-black text-[var(--heading)]">Sign in to manage your reports</h2>
        <p class="mt-2 text-sm text-slate-600">This authentication flow is now powered by Laravel Breeze.</p>

        <x-auth-session-status class="mb-4 mt-6" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-4">
            @csrf
            <div>
                <label class="label" for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="field" required autofocus autocomplete="username">
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <div class="password-field-wrap">
                    <input id="password" type="password" name="password" class="field password-field-input" required autocomplete="current-password" data-password-input>
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
            <label class="flex items-center gap-3 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300">
                Keep me signed in
            </label>

            <div class="flex items-center justify-between gap-4">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-600 hover:underline">Forgot password?</a>
                @endif
                <button type="submit" class="primary-button">Login</button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            No account yet?
            <a href="{{ route('register') }}" class="font-bold text-[var(--accent)]">Create one here</a>
        </p>
    </section>
@endsection
