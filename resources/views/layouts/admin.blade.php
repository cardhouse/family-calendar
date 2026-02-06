<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name').' Admin' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800,900" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance

        @livewireStyles
    </head>
    <body class="min-h-screen bg-amber-50/30 dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-r border-amber-200/60 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <div class="flex items-center gap-2">
                    <flux:icon name="home" variant="outline" class="size-5 text-amber-600" />
                    <div>
                        <flux:heading size="lg" class="font-extrabold">Family Calendar</flux:heading>
                        <flux:text class="text-xs uppercase tracking-[0.2em] text-slate-400">Settings</flux:text>
                    </div>
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="arrow-left" href="{{ route('home') }}">Back to dashboard</flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" href="{{ route('admin.settings') }}" :current="request()->routeIs('admin.settings')">Settings</flux:sidebar.item>
                <flux:sidebar.item icon="face-smile" href="{{ route('admin.children') }}" :current="request()->routeIs('admin.children')">Children</flux:sidebar.item>
                <flux:sidebar.item icon="clock" href="{{ route('admin.departures') }}" :current="request()->routeIs('admin.departures')">Departures</flux:sidebar.item>
                <flux:sidebar.item icon="calendar" href="{{ route('admin.events') }}" :current="request()->routeIs('admin.events')">Events</flux:sidebar.item>
                <flux:sidebar.item icon="clipboard-document-check" href="{{ route('admin.routines') }}" :current="request()->routeIs('admin.routines')">Routines</flux:sidebar.item>
                <flux:sidebar.item icon="cloud" href="{{ route('admin.weather') }}" :current="request()->routeIs('admin.weather')">Weather</flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:heading class="font-extrabold">Family Calendar</flux:heading>
            <flux:spacer />
        </flux:header>

        <flux:main>
            {{ $slot }}
        </flux:main>

        @fluxScripts
        @livewireScripts
    </body>
</html>
