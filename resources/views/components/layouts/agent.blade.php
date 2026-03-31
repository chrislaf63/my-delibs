<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}Espace agents — {{ config('app.name', 'My Délibs') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_ccpl.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 overflow-hidden">

    <nav class="bg-white border-b border-gray-200 shadow-sm h-14">
        <div class="px-4 sm:px-6 lg:px-8 h-full">
            <div class="flex items-center justify-between h-full">
                <a href="{{ route('agent.explorer') }}"
                   class="flex items-center gap-2 text-indigo-700 hover:text-indigo-900 transition">
                    <img alt="logo ccpl" src="{{ asset('images/logo_ccpl.png') }}" class="h-8 w-auto">
                    <span class="text-sm font-semibold hidden sm:inline">Espace agents</span>
                </a>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <span class="text-indigo-700 font-semibold">
                        Explorateur
                    </span>
                    <a href="{{ route('public.council.index') }}"
                       class="text-gray-500 hover:text-indigo-700 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Site public
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div style="height: calc(100vh - 3.5rem);">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>