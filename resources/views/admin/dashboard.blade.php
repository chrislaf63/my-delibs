<x-app-layout>
    <div class="flex items-center mb-6">
        <h1 class="text-2xl font-bold">Tableau de bord</h1>
    </div>

    <div class="grid grid-cols-2 gap-8 mb-10">
        <div class="bg-white p-6 shadow rounded">
            <h2 class="text-lg font-semibold mb-4">Créer une séance</h2>

            <form method="POST" action="{{ route('admin.councils.store') }}">
                @csrf

                <input type="date" name="council_date"
                       class="w-full border p-2 mb-3" required>

                <button class="bg-ccpl-blue text-white px-4 py-2 rounded hover:opacity-70 transition">
                    Créer
                </button>
            </form>
        </div>
        <div class="bg-white p-6 shadow rounded">
            <h2 class="text-lg font-semibold mb-4">Derniers documents indexés</h2>

            @forelse($recentIndexedDocuments as $doc)
                <div class="py-2 border-b last:border-0">
                    <p class="font-medium text-sm truncate">{{ $doc->title }}</p>
                    <p class="text-xs text-gray-500">
                        Séance du {{ $doc->council->council_date->format('d/m/Y') }}
                        &bull; {{ $doc->type === 'deliberation' ? 'Délibération' : 'Procès-verbal' }}
                        &bull; indexé le {{ $doc->indexed_at->format('d/m/Y') }}
                        @if($doc->uploader)
                            &bull; par {{ trim($doc->uploader->first_name . ' ' . $doc->uploader->name) }}
                        @endif
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucun document indexé.</p>
            @endforelse
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="grid grid-cols-5 gap-6 mb-10">

        <div class="bg-white p-6 shadow rounded">
            <p class="text-sm text-gray-500">Séances</p>
            <p class="text-3xl font-bold">{{ $totalCouncils }}</p>
        </div>

        <div class="bg-white p-6 shadow rounded">
            <p class="text-sm text-gray-500">Documents</p>
            <p class="text-3xl font-bold">{{ $totalDocuments }}</p>
        </div>

        <div class="bg-white p-6 shadow rounded">
            <p class="text-sm text-gray-500">Procès-verbaux</p>
            <p class="text-3xl font-bold">{{ $totalProcesVerbaux }}</p>
        </div>

        <div class="bg-white p-6 shadow rounded">
            <p class="text-sm text-gray-500">Délibérations</p>
            <p class="text-3xl font-bold">{{ $totalDeliberations }}</p>
        </div>

        <div class="bg-white p-6 shadow rounded">
            <p class="text-sm text-gray-500">Annexes</p>
            <p class="text-3xl font-bold">{{ $totalAnnexes }}</p>
        </div>

    </div>

    <!-- Bloc indexation -->
    <div class="grid grid-cols-3 gap-6 mb-10">

        <div class="bg-yellow-50 p-6 rounded shadow">
            <p class="text-sm">En attente / en cours</p>
            <p class="text-2xl font-bold" id="stat-pending">{{ $pendingDocuments }}</p>
        </div>

        <div class="bg-green-50 p-6 rounded shadow">
            <p class="text-sm">Documents indexés</p>
            <p class="text-2xl font-bold" id="stat-indexed">{{ $indexedDocuments }}</p>
        </div>

        <div class="bg-red-50 p-6 rounded shadow">
            <p class="text-sm">Documents en erreur</p>
            <p class="text-2xl font-bold" id="stat-failed">{{ $failedDocuments }}</p>
        </div>

    </div>

    <script>
        (function () {
            const url = ‘{{ route("admin.dashadmin.status") }}’;

            function refresh() {
                fetch(url, { headers: { ‘X-Requested-With’: ‘XMLHttpRequest’ } })
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById(‘stat-pending’).textContent = data.pendingDocuments;
                        document.getElementById(‘stat-indexed’).textContent = data.indexedDocuments;
                        document.getElementById(‘stat-failed’).textContent  = data.failedDocuments;
                    })
                    .catch(() => {});
            }

            setInterval(refresh, 5000);
        })();
    </script>

    <!-- Dernières séances -->
    <h2 class="text-xl font-semibold mb-3">Dernières séances</h2>

    <div class="bg-white shadow rounded mb-8">
        @foreach($recentCouncils as $council)
            <div class="p-4 border-b flex justify-between">
            <span>
                Séance du {{ $council->council_date->format('d/m/Y') }}
            </span>

                <a href="{{ route('admin.councils.show', $council) }}"
                   class="inline-flex items-center gap-1 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Voir
                </a>
            </div>
        @endforeach
    </div>

</x-app-layout>
