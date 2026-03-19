<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Liste des séances</h1>
            <a href="{{ route('admin.dashadmin') }}" class="text-sm text-blue-600 hover:underline">
                ← Tableau de bord
            </a>
        </div>

        @if($councilsByYear->isEmpty())
            <p class="text-gray-500">Aucune séance enregistrée.</p>
        @else
            <div class="space-y-4">
                @foreach($councilsByYear as $year => $councils)
                    <details class="rounded-lg border border-gray-200 shadow-sm" {{ $loop->first ? 'open' : '' }}>
                        <summary class="flex items-center justify-between px-4 py-3 cursor-pointer list-none select-none bg-gray-100 rounded-lg">
                            <span class="text-lg font-bold text-gray-700">{{ $year }}</span>
                            <span class="text-sm text-gray-500">
                                {{ $councils->count() }} séance{{ $councils->count() > 1 ? 's' : '' }}
                            </span>
                        </summary>
                        <div class="space-y-2 p-3">
                @foreach($councils as $council)
                    <details class="bg-white shadow rounded group">
                        <summary class="flex items-center justify-between p-4 cursor-pointer list-none select-none">
                            <span class="font-medium">
                                Séance du {{ $council->council_date->translatedFormat('j F Y') }}
                            </span>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-400">
                                    {{ $council->documents->count() }}
                                    {{ $council->documents->count() > 1 ? 'documents' : 'document' }}
                                </span>
                                <button type="button"
                                        onclick="event.preventDefault(); openCouncilDeleteModal({{ $council->id }}, '{{ $council->council_date->format('d/m/Y') }}', {{ $council->documents->count() }})"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded text-white bg-red-600 hover:bg-red-700 transition"
                                        title="Supprimer la séance">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </summary>

                        {{-- Liste des documents --}}
                        @if($council->documents->isNotEmpty())
                            <ul class="border-t divide-y">
                                @foreach($council->documents as $document)
                                    @php
                                        $rowClass = match($document->status) {
                                            'pending'    => 'border-l-4 border-yellow-400 bg-yellow-50',
                                            'processing' => 'border-l-4 border-blue-400 bg-blue-50',
                                            'failed'     => 'border-l-4 border-red-400 bg-red-50',
                                            default      => '',
                                        };
                                    @endphp
                                    <li>
                                        <div class="flex items-center justify-between px-4 py-3 {{ $rowClass }}">
                                            <div>
                                                <p class="text-sm font-medium">{{ $document->title }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ match($document->type) {
                                                        'deliberation' => 'Délibération',
                                                        'proces_verbal' => 'Procès-verbal',
                                                        'annexe' => 'Annexe',
                                                    } }}
                                                    &middot;
                                                    @switch($document->status)
                                                        @case('indexed')
                                                            <span class="text-green-600 font-medium">Indexé</span>
                                                            @break
                                                        @case('pending')
                                                            <span class="text-yellow-700 font-medium">En attente d'indexation</span>
                                                            @break
                                                        @case('processing')
                                                            <span class="text-blue-700 font-medium">Indexation en cours…</span>
                                                            @break
                                                        @case('failed')
                                                            <span class="text-red-700 font-medium">Échec de l'indexation</span>
                                                            @break
                                                    @endswitch
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                @if($document->status === 'indexed')
                                                    <a href="{{ route('public.documents.view', $document) }}"
                                                       target="_blank"
                                                       class="text-sm text-blue-600 hover:underline">
                                                        Voir
                                                    </a>
                                                @endif

                                                @if($document->status === 'failed')
                                                    <form method="POST" action="{{ route('admin.documents.reindex', $document) }}">
                                                        @csrf
                                                        <button type="submit"
                                                                class="text-sm text-orange-600 hover:underline">
                                                            Relancer
                                                        </button>
                                                    </form>
                                                @endif

                                                <button type="button"
                                                        onclick="openDeleteModal({{ $document->id }}, '{{ addslashes($document->title) }}')"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded text-white bg-red-600 hover:bg-red-700 transition"
                                                        title="Supprimer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        @if($document->type === 'deliberation' && $document->annexes->isNotEmpty())
                                            <details class="border-t border-gray-100 group/annexe">
                                                <summary class="flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-indigo-600 cursor-pointer hover:bg-indigo-50 transition list-none select-none">
                                                    <svg class="h-3.5 w-3.5 transition-transform group-open/annexe:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    {{ $document->annexes->count() }} annexe{{ $document->annexes->count() > 1 ? 's' : '' }}
                                                </summary>
                                                <ul class="divide-y divide-gray-100 bg-gray-50">
                                                    @foreach($document->annexes as $annexe)
                                                        @php
                                                            $annexeRowClass = match($annexe->status) {
                                                                'pending'    => 'border-l-4 border-yellow-400 bg-yellow-50',
                                                                'processing' => 'border-l-4 border-blue-400 bg-blue-50',
                                                                'failed'     => 'border-l-4 border-red-400 bg-red-50',
                                                                default      => '',
                                                            };
                                                        @endphp
                                                        <li class="flex items-center justify-between pl-8 pr-4 py-2 {{ $annexeRowClass }}">
                                                            <div>
                                                                <p class="text-sm text-gray-700">{{ $annexe->title }}</p>
                                                                <p class="text-xs text-gray-400">
                                                                    @switch($annexe->status)
                                                                        @case('indexed') <span class="text-green-600 font-medium">Indexé</span> @break
                                                                        @case('pending') <span class="text-yellow-700 font-medium">En attente</span> @break
                                                                        @case('processing') <span class="text-blue-700 font-medium">En cours…</span> @break
                                                                        @case('failed') <span class="text-red-700 font-medium">Échec</span> @break
                                                                    @endswitch
                                                                </p>
                                                            </div>
                                                            <div class="flex items-center gap-3">
                                                                @if($annexe->status === 'indexed')
                                                                    <a href="{{ route('public.documents.view', $annexe) }}" target="_blank"
                                                                       class="text-sm text-blue-600 hover:underline">Voir</a>
                                                                @endif
                                                                @if($annexe->status === 'failed')
                                                                    <form method="POST" action="{{ route('admin.documents.reindex', $annexe) }}">
                                                                        @csrf
                                                                        <button type="submit" class="text-sm text-orange-600 hover:underline">Relancer</button>
                                                                    </form>
                                                                @endif
                                                                <button type="button"
                                                                        onclick="openDeleteModal({{ $annexe->id }}, '{{ addslashes($annexe->title) }}')"
                                                                        class="inline-flex items-center justify-center w-8 h-8 rounded text-white bg-red-600 hover:bg-red-700 transition"
                                                                        title="Supprimer">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Formulaire d'ajout de document --}}
                        <div class="border-t p-4 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ajouter un document</p>
                            <x-form-upload :council="$council" />
                        </div>

                    </details>
                @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        @endif

    </div>

    {{-- Modale de confirmation de suppression --}}
    <div id="delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le document</h2>
            <p class="text-sm text-gray-600 mb-1">Voulez-vous vraiment supprimer :</p>
            <p id="delete-modal-title" class="text-sm font-medium text-gray-900 mb-4"></p>
            <p class="text-sm text-red-600 mb-6">Cette action est irréversible. Le fichier sera supprimé du stockage et de la base de données.</p>

            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeDeleteModal()"
                            class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm rounded bg-red-600 text-white hover:bg-red-700 transition">
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modale de confirmation — suppression séance --}}
    <div id="council-delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Supprimer la séance</h2>
            <p class="text-sm text-gray-600 mb-1">Voulez-vous vraiment supprimer la séance du :</p>
            <p id="council-delete-modal-date" class="text-sm font-medium text-gray-900 mb-2"></p>
            <p id="council-delete-modal-warning" class="text-sm text-red-600 mb-6"></p>

            <form id="council-delete-form" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeCouncilDeleteModal()"
                            class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm rounded bg-red-600 text-white hover:bg-red-700 transition">
                        Supprimer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modale documents
        const modal = document.getElementById('delete-modal');
        const form  = document.getElementById('delete-form');
        const title = document.getElementById('delete-modal-title');
        const base  = '{{ rtrim(route("admin.documents.destroy", ["document" => "__ID__"]), "") }}';

        function openDeleteModal(id, documentTitle) {
            form.action = base.replace('__ID__', id);
            title.textContent = documentTitle;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeDeleteModal();
        });

        // Modale séances
        const councilModal   = document.getElementById('council-delete-modal');
        const councilForm    = document.getElementById('council-delete-form');
        const councilDate    = document.getElementById('council-delete-modal-date');
        const councilWarning = document.getElementById('council-delete-modal-warning');
        const councilBase    = '{{ rtrim(route("admin.councils.destroy", ["council" => "__ID__"]), "") }}';

        function openCouncilDeleteModal(id, date, docCount) {
            councilForm.action = councilBase.replace('__ID__', id);
            councilDate.textContent = date;
            councilWarning.textContent = docCount > 0
                ? 'Cette action est irréversible. Les ' + docCount + ' document(s) rattaché(s) seront également supprimés du stockage et de la base de données.'
                : 'Cette action est irréversible.';
            councilModal.classList.remove('hidden');
            councilModal.classList.add('flex');
        }

        function closeCouncilDeleteModal() {
            councilModal.classList.add('hidden');
            councilModal.classList.remove('flex');
        }

        councilModal.addEventListener('click', function (e) {
            if (e.target === councilModal) closeCouncilDeleteModal();
        });
    </script>
</x-app-layout>
