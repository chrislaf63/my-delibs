<div {{ $this->hasPendingDocuments ? 'wire:poll.5s' : '' }}
     x-data="{ showDeleteModal: false, deleteId: null, deleteTitle: '' }">

    @if($documents->isEmpty())
        <p class="px-4 py-3 text-sm text-gray-500">Aucun document.</p>
    @else
        <ul class="border-t divide-y">
            @foreach($documents as $document)
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
                                    'deliberation'  => 'Délibération',
                                    'proces_verbal' => 'Procès-verbal',
                                    'annexe'        => 'Annexe',
                                } }}

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
                                        @if($document->uploader)
                                            &middot;
                                        {{ trim('Ajouté par ' . $document->uploader->first_name . ' ' . $document->uploader->name) }}
                                        @endif
                                        &middot;
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
                                <button type="button"
                                        wire:click="reindexDocument({{ $document->id }})"
                                        class="text-sm text-orange-600 hover:underline">
                                    Relancer
                                </button>
                            @endif

                            <button type="button"
                                    @click="deleteId = {{ $document->id }}; deleteTitle = '{{ addslashes($document->title) }}'; showDeleteModal = true"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded text-white bg-red-600 hover:bg-red-700 transition"
                                    title="Supprimer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if($document->type === 'deliberation' && $document->annexes->isNotEmpty())
                        <details class="border-t border-gray-100 group/annexe">
                            <summary
                                class="flex items-center gap-1.5 px-4 py-2 text-xs font-medium text-indigo-600 cursor-pointer hover:bg-indigo-50 transition list-none select-none">
                                <svg class="h-3.5 w-3.5 transition-transform group-open/annexe:rotate-90"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
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
                                                    @case('indexed')    <span
                                                        class="text-green-600 font-medium">Indexé</span>    @break
                                                    @case('pending')    <span class="text-yellow-700 font-medium">En attente</span> @break
                                                    @case('processing') <span class="text-blue-700 font-medium">En cours…</span>   @break
                                                    @case('failed')     <span
                                                        class="text-red-700 font-medium">Échec</span>        @break
                                                    @endswitch
                                                    @if($annexe->uploader)
                                                        {{ trim('Ajouté par '.$annexe->uploader->first_name . ' ' . $annexe->uploader->name) }} &middot;
                                                    @endif
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            @if($annexe->status === 'indexed')
                                                <a href="{{ route('public.documents.view', $annexe) }}" target="_blank"
                                                   class="text-sm text-blue-600 hover:underline">Voir</a>
                                            @endif
                                            @if($annexe->status === 'failed')
                                                <button type="button"
                                                        wire:click="reindexDocument({{ $annexe->id }})"
                                                        class="text-sm text-orange-600 hover:underline">
                                                    Relancer
                                                </button>
                                            @endif
                                            <button type="button"
                                                    @click="deleteId = {{ $annexe->id }}; deleteTitle = '{{ addslashes($annexe->title) }}'; showDeleteModal = true"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded text-white bg-red-600 hover:bg-red-700 transition"
                                                    title="Supprimer">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
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

    {{-- Modale de suppression de document --}}
    <div x-show="showDeleteModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showDeleteModal = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Supprimer le document</h2>
            <p class="text-sm text-gray-600 mb-1">Voulez-vous vraiment supprimer :</p>
            <p x-text="deleteTitle" class="text-sm font-medium text-gray-900 mb-4"></p>
            <p class="text-sm text-red-600 mb-6">Cette action est irréversible. Le fichier sera supprimé du stockage et
                de la base de données.</p>

            <div class="flex justify-end gap-3">
                <button type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="button"
                        @click="$wire.deleteDocument(deleteId); showDeleteModal = false"
                        class="px-4 py-2 text-sm rounded bg-red-600 text-white hover:bg-red-700 transition">
                    Supprimer
                </button>
            </div>
        </div>
    </div>

</div>
