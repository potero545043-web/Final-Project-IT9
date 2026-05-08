<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Campus Lost and Found' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[var(--surface)] text-slate-900">
    <div class="page-shell">
        @auth
            @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
            <div class="dashboard-shell">
                <aside class="sidebar">
                    <a href="{{ route('home') }}" class="sidebar-brand">
                        <span class="sidebar-logo">LF</span>
                        <span>
                            <span class="sidebar-brand-top">Campus Property Desk</span>
                            <span class="sidebar-brand-name">Lost and Found System</span>
                        </span>
                    </a>

                    <nav class="sidebar-nav">
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">WS</span>
                            <span>{{ auth()->user()->isAdmin() ? 'Admin Dashboard' : 'My Dashboard' }}</span>
                        </a>
                        <a href="{{ route('home') }}" class="sidebar-link {{ request()->routeIs('home') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">AE</span>
                            <span>Browse Items</span>
                        </a>
                        <a href="{{ route('items.mine') }}" class="sidebar-link {{ request()->routeIs('items.mine') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">MR</span>
                            <span>My Report</span>
                        </a>
                        <a href="{{ route('items.resolved') }}" class="sidebar-link {{ request()->routeIs('items.resolved') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">RC</span>
                            <span>{{ auth()->user()->isAdmin() ? 'Resolved Cases' : 'My Resolved Reports' }}</span>
                        </a>
                        <a href="{{ route('items.archived') }}" class="sidebar-link {{ request()->routeIs('items.archived') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">AR</span>
                            <span>{{ auth()->user()->isAdmin() ? 'Deleted Reports' : 'My Deleted Reports' }}</span>
                        </a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.create') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : '' }}">
                                <span class="sidebar-icon">UA</span>
                                <span>Create Account</span>
                            </a>
                        @endif
                        <a href="{{ route('items.create') }}" class="sidebar-link {{ request()->routeIs('items.create') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">NR</span>
                            <span>Report Item</span>
                        </a>
                        <a href="{{ route('notifications.index') }}" class="sidebar-link {{ request()->routeIs('notifications.*') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">NT</span>
                            <span>Notification</span>
                            @if ($unreadNotifications > 0)
                                <span class="sidebar-badge">{{ $unreadNotifications }}</span>
                            @endif
                        </a>
                        <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'sidebar-link-active' : '' }}">
                            <span class="sidebar-icon">PF</span>
                            <span>Profile</span>
                        </a>
                    </nav>

                    <div class="sidebar-meta">
                        <p class="sidebar-meta-label">Signed in as</p>
                        <p class="sidebar-meta-name">{{ auth()->user()->name }}</p>
                        <p class="sidebar-meta-role">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Student User' }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-logout">Logout</button>
                    </form>
                </aside>

                <div class="dashboard-main">
                    <header class="workspace-header">
                        <div>
                            <p class="workspace-label">{{ auth()->user()->isAdmin() ? 'Admin Console' : 'Student Portal' }}</p>
                        </div>
                    </header>

                    <main class="dashboard-content">
                        @if (session('success'))
                            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                                <p class="font-semibold">Please review the highlighted information.</p>
                                <ul class="mt-2 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @isset($header)
                            <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                {{ $header }}
                            </div>
                        @endisset

                        {{ $slot ?? '' }}
                        @yield('content')
                    </main>
                </div>
            </div>
        @else
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:px-8 lg:flex-row lg:items-center lg:justify-between">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--accent)] text-lg font-black text-white">LF</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Campus Property Desk</p>
                            <h1 class="text-xl font-black text-[var(--heading)]">Lost and Found System</h1>
                        </div>
                    </a>

                    <nav class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-700">
                        <a href="{{ route('home') }}" class="nav-tab {{ request()->routeIs('home') ? 'nav-tab-active' : '' }}">Home</a>
                        <a href="{{ route('login') }}" class="nav-tab {{ request()->routeIs('login') ? 'nav-tab-active' : '' }}">Login</a>
                        <a href="{{ route('register') }}" class="primary-button">Create Account</a>
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                        <p class="font-semibold">Please review the highlighted information.</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        @endauth
    </div>
</body>
</html>
