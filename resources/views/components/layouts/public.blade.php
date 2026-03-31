<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'My Délibs').' CCPL' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_ccpl.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('public.council.index') }}"
                   class="text-lg font-semibold text-indigo-700 hover:text-indigo-900 transition">
                    <img alt="logo ccpl" src="{{ asset('images/logo_ccpl.png') }}" width="30%">
                </a>
                <div class="flex items-center gap-6 text-sm font-medium">
                    <a href="{{ route('public.council.index') }}"
                       class="{{ request()->routeIs('public.council.index') ? 'text-ccpl-blue font-semibold' : 'text-ccpl-brown hover:text-ccpl-blue transition-colors duration-300' }}">
                        Séances
                    </a>
                    <a href="{{ route('search.index') }}"
                       class="{{ request()->routeIs('search.index') ? 'text-ccpl-blue font-semibold' : 'text-ccpl-brown hover:text-ccpl-blue transition-colors duration-300' }}">
                        Recherche
                    </a>
                </div>
            </div>
        </div>
    </nav>

    @if (isset($header))
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-5xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="mt-16 border-t border-gray-200 bg-white">
        <div class="max-w-5xl mx-auto py-6 px-4 text-center text-sm text-ccpl-brown">
            &copy; {{ date('Y') }} {{ config('app.name', 'My Délibs') . ' Communauté de Communes Plaine Limagne' }}
        </div>
    </footer>

    @livewireScripts
</body>
</html>
