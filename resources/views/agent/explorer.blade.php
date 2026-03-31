<x-layouts.agent>
@php
    $docsJson = $documents->map(fn ($doc) => [
        'id'           => $doc->id,
        'title'        => $doc->title,
        'type'         => $doc->type,
        'council_date' => $doc->council->council_date->translatedFormat('d F Y'),
        'view_url'     => route('public.documents.view', $doc),
        'download_url' => route('public.documents.download', $doc),
        'annexes'      => $doc->annexes->map(fn ($a) => [
            'id'           => $a->id,
            'title'        => $a->title,
            'view_url'     => route('public.documents.view', $a),
            'download_url' => route('public.documents.download', $a),
        ])->values()->toArray(),
    ])->keyBy('id')->toArray();
@endphp

<div
    x-data="agentExplorer(@js($docsJson))"
    class="flex h-full"
>
    {{-- EXPLORER (70%) --}}
    <aside style="width: 70%;" class="shrink-0 bg-white border-r border-gray-200 overflow-y-auto flex flex-col">
        <div class="p-3 border-b border-gray-100">
            <p class="text-xs font-semibold text-ccpl-brown uppercase tracking-wider">Documents</p>
        </div>
        <div class="flex-1 py-2">
            @foreach ($tree as $type => $group)
                {{-- ROOT FOLDER --}}
                <div>
                    <button
                        @click="toggleFolder('{{ $type }}')"
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                    >
                        <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-150"
                             :class="{ 'rotate-90': isOpen('{{ $type }}') }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <svg class="w-4 h-4 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        <span>{{ $group['label'] }}</span>
                    </button>

                    <div x-show="isOpen('{{ $type }}')" x-transition>
                        @foreach ($group['years'] as $year => $docs)
                            {{-- YEAR FOLDER --}}
                            <div>
                                <button
                                    @click="toggleFolder('{{ $type }}-{{ $year }}')"
                                    class="w-full flex items-center gap-2 pl-7 pr-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                                >
                                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform duration-150"
                                         :class="{ 'rotate-90': isOpen('{{ $type }}-{{ $year }}') }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <svg class="w-4 h-4 text-yellow-300 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                                    </svg>
                                    <span class="font-medium">{{ $year }}</span>
                                    <span class="ml-auto text-xs text-gray-400">{{ $docs->count() }}</span>
                                </button>

                                <div x-show="isOpen('{{ $type }}-{{ $year }}')" x-transition>
                                    @foreach ($docs as $doc)
                                        <button
                                            @click="selectDoc({{ $doc->id }})"
                                            :class="{ 'bg-indigo-50 text-indigo-700': selected === {{ $doc->id }} }"
                                            class="w-full flex items-center gap-2 pl-14 pr-3 py-1.5 text-left text-sm text-gray-600 hover:bg-gray-50 transition"
                                        >
                                            <svg class="w-4 h-4 shrink-0"
                                                 :class="selected === {{ $doc->id }} ? 'text-indigo-400' : 'text-gray-400'"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="truncate leading-tight">{{ $doc->title }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </aside>

    {{-- PRÉVISUALISATION (30%) --}}
    <main style="width: 30%;" class="shrink-0 overflow-y-auto bg-gray-50">

        {{-- Empty state --}}
        <template x-if="!selected">
            <div class="flex flex-col items-center justify-center h-full text-center text-gray-400">
                <svg class="w-16 h-16 mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <p class="text-sm font-medium">Sélectionnez un document dans l'arborescence</p>
                <p class="text-xs mt-1">Cliquez sur un dossier pour le déplier, puis sur un document</p>
            </div>
        </template>

        {{-- Document detail --}}
        <template x-if="selected && currentDoc">
            <div class="h-full flex flex-col">
                {{-- Header --}}
                <div class="bg-white border-b border-gray-200 px-4 py-3">
                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">
                        <span x-text="currentDoc.type === 'deliberation' ? 'Délibération' : 'Procès-verbal'"></span>
                        · <span x-text="currentDoc.council_date"></span>
                    </p>
                    <h1 class="text-sm font-semibold text-gray-900 leading-snug mb-3" x-text="currentDoc.title"></h1>
                    <div class="flex gap-2">
                        <a :href="currentDoc.view_url" target="_blank"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg bg-ccpl-blue opacity-80 text-white text-xs font-medium hover:opacity-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Consulter
                        </a>
                        <a :href="currentDoc.download_url"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-lg bg-white border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Télécharger
                        </a>
                    </div>

                    {{-- Annexes --}}
                    <template x-if="currentDoc.annexes && currentDoc.annexes.length > 0">
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">
                                Annexes (<span x-text="currentDoc.annexes.length"></span>)
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="annexe in currentDoc.annexes" :key="annexe.id">
                                    <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-xs text-gray-600" x-text="annexe.title"></span>
                                        <a :href="annexe.view_url" target="_blank"
                                           class="text-indigo-500 hover:text-indigo-700 ml-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a :href="annexe.download_url"
                                           class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- PDF Viewer --}}
                <div class="flex-1 p-4">
                    <iframe
                        :src="currentDoc.view_url"
                        class="w-full h-full rounded-lg border border-gray-200 shadow-sm bg-white"
                    ></iframe>
                </div>
            </div>
        </template>
    </main>
</div>

<script>
function agentExplorer(docs) {
    return {
        docs: docs,
        openFolders: {},
        selected: null,
        currentDoc: null,

        toggleFolder(key) {
            this.openFolders[key] = !this.openFolders[key];
        },

        isOpen(key) {
            return !!this.openFolders[key];
        },

        selectDoc(id) {
            this.selected = id;
            this.currentDoc = this.docs[id] ?? null;
        },
    };
}
</script>
</x-layouts.agent>
