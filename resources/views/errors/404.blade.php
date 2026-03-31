<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page introuvable — {{ config('app.name', 'My Délibs') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_ccpl.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css'])
</head>
<body class="font-petita antialiased bg-gray-50 text-gray-900 flex flex-col min-h-screen">
<main class="flex-1 flex flex-col items-center justify-center">
    <div class="mt-20">
        <img alt="logo ccpl" src="{{ asset('images/logo_ccpl.png') }}" width="150">
    </div>

    <div class="flex-1 flex flex-col items-center px-4">
        <div class="pt-10 text-center">
            <p class="text-8xl font-bold text-ccpl-blue opacity-20 leading-none select-none">404</p>
            <h1 class="mt-4 text-2xl font-bold text-gray-800">Page introuvable</h1>
            <p class="mt-2 text-gray-500">La page que vous recherchez n'existe pas ou a été déplacée.</p>
            <a href="/"
               class="mt-8 inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-ccpl-blue text-white text-sm font-medium hover:opacity-80 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à l'accueil
            </a>
        </div>
    </div>
</main>
<footer class="border-t border-gray-200 bg-white min-h">
    <div class="max-w-5xl mx-auto py-6 px-4 text-center text-sm text-ccpl-brown">
        &copy; {{ date('Y') }} {{ config('app.name', 'My Délibs') . ' Communauté de Communes Plaine Limagne' }}
    </div>
</footer>

</body>
</html>
