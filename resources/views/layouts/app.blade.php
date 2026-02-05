<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=nunito:400,600,700,800,900" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance

        @livewireStyles
    </head>
    <body class="min-h-screen bg-dash-bg text-slate-100 antialiased">
        <div class="pointer-events-none fixed inset-0 bg-[radial-gradient(ellipse_at_top,_rgba(59,130,246,0.07)_0%,_transparent_60%)]"></div>
        <div class="relative min-h-screen">
            {{ $slot }}
        </div>

        @fluxScripts
        @livewireScripts
    </body>
</html>
