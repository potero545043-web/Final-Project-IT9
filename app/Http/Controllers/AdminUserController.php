<?php

namespace App\Http\Controllers;

use App\Models\AccountCreationLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function create(): View
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $recentLogs = AccountCreationLog::query()
            ->with(['createdUser', 'createdBy'])
            ->latest()
            ->take(8)
            ->get();

        return view('admin.users.create', compact('recentLogs'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->isAdmin(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:50', 'unique:users,student_id'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:student,admin'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $admin = Auth::user();

        DB::transaction(function () use ($validated, $admin): void {
            $user = User::create([
                'name' => $validated['name'],
                'student_id' => $validated['student_id'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
            ]);

            AccountCreationLog::create([
                'created_user_id' => $user->id,
                'created_by_user_id' => $admin->id,
                'assigned_role' => $validated['role'],
            ]);
        });

        return redirect()
            ->route('admin.users.create')
            ->with('success', ucfirst($validated['role']).' account created successfully.');
    }
}
