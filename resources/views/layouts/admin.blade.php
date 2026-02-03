<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name').' Admin' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <div class="flex min-h-screen">
            <aside class="flex w-64 flex-col gap-6 border-r border-slate-200 bg-white px-6 py-8">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-400">Family Calendar</div>
                    <div class="text-2xl font-semibold text-slate-900">Admin</div>
                </div>
                <nav class="flex flex-col gap-2 text-sm">
                    <a class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100" href="{{ route('admin.children') }}">Children</a>
                    <a class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100" href="{{ route('admin.departures') }}">Departures</a>
                    <a class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100" href="{{ route('admin.events') }}">Events</a>
                    <a class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100" href="{{ route('admin.routines') }}">Routines</a>
                    <a class="rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100" href="{{ route('admin.weather') }}">Weather</a>
                </nav>
            </aside>

            <main class="flex-1 px-8 py-10">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
