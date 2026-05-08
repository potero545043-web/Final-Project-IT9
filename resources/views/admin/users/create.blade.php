@extends('layouts.app')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <section class="rounded-[2rem] border border-white/60 bg-white/90 p-8 shadow-xl shadow-slate-200">
            <p class="badge">Admin Tool</p>
            <h2 class="mt-4 text-3xl font-black text-[var(--heading)]">Create a new user account</h2>
            <p class="mt-2 text-sm text-slate-600">Use this page to create a student or administrator account without using the public registration form.</p>

            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                <p class="font-bold">Only authorized personnel should assign admin role.</p>
                <p class="mt-1">Use the admin option only for trusted staff members who require full system control.</p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-8 grid gap-4 md:grid-cols-2" data-admin-account-form>
                @csrf

                <div class="md:col-span-2">
                    <label class="label" for="name">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" class="field" required autofocus>
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

                <div class="md:col-span-2">
                    <label class="label" for="role">Account role</label>
                    <select id="role" name="role" class="field" required data-admin-role-select>
                        <option value="student" @selected(old('role', 'student') === 'student')>Student</option>
                        <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Select the role carefully before creating the account.</p>
                    <x-input-error :messages="$errors->get('role')" class="mt-2 text-sm text-rose-600" />
                </div>

                <div class="md:col-span-2 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-900" data-admin-role-warning hidden>
                    <p class="font-bold">This user will have full system access.</p>
                    <p class="mt-1">Admins can review reports, approve claims, resolve cases, and create other accounts.</p>
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
                    <a href="{{ route('dashboard') }}" class="secondary-button">Back to dashboard</a>
                    <button type="submit" class="primary-button">Create account</button>
                </div>
            </form>
        </section>

        <section class="console-panel">
            <div class="console-panel-header">
                <div>
                    <p class="console-label">Account Audit Trail</p>
                    <h3 class="console-title">Recent account creation activity</h3>
                </div>
            </div>

            <div class="console-table-wrap">
                <table class="console-table">
                    <thead>
                        <tr>
                            <th>Created User</th>
                            <th>Role</th>
                            <th>Created By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentLogs as $log)
                            <tr>
                                <td class="table-title-cell">{{ $log->createdUser->name ?? 'Deleted user' }}</td>
                                <td><span class="role-badge role-badge-{{ $log->assigned_role }}">{{ ucfirst($log->assigned_role) }}</span></td>
                                <td>{{ $log->createdBy->name ?? 'Unknown admin' }}</td>
                                <td>{{ $log->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-slate-500">No account creation activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
