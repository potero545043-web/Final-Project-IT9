<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--surface)] text-slate-900">
        <div class="top-strip">
            <p class="top-strip-title">Lost and Found Management Portal</p>
            <p class="top-strip-text">Organized reporting, inquiry, and claim verification</p>
        </div>

        <div class="mx-auto flex min-h-[calc(100vh-30px)] max-w-7xl items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
