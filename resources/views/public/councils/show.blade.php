<x-layouts.public>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('public.council.index') }}"
               class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                &larr; Séances
            </a>
            <span class="text-gray-300">/</span>
            <h1 class="text-2xl font-bold text-gray-800">
                Séance du {{ $council->council_date->translatedFormat('d F Y') }}
            </h1>
        </div>
        @if ($council->reference)
            <p class="mt-1 text-sm text-gray-500">{{ $council->reference }}</p>
        @endif
    </x-slot>

    @if ($deliberations->isEmpty() && $procesVerbaux->isEmpty())
        <p class="text-gray-500 text-center py-12">Aucun document disponible pour cette séance.</p>
    @else
        @if ($deliberations->isNotEmpty())
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Délibérations</h2>
                <ul class="divide-y divide-gray-200 bg-white rounded-xl shadow-sm overflow-hidden">
                    @foreach ($deliberations as $document)
                        <li>
                            <div class="flex items-center justify-between px-6 py-4 gap-4 hover:bg-gray-50 transition">
                                <span class="text-gray-800">{{ $document->title }}</span>
                                <div class="shrink-0 flex items-center gap-3">
                                    <a href="{{ route('public.documents.view', $document) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-indigo-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Consulter
                                    </a>
                                    <a href="{{ route('public.documents.download', $document) }}"
                                       class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                        </svg>
                                        Télécharger
                                    </a>
                                </div>
                            </div>

                            @if($document->annexes->isNotEmpty())
                                <details class="border-t border-gray-100 group/annexe">
                                    <summary class="flex items-center gap-1.5 px-6 py-2 text-xs font-medium text-indigo-600 cursor-pointer hover:bg-indigo-50 transition list-none select-none">
                                        <svg class="h-3.5 w-3.5 transition-transform group-open/annexe:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                        Voir les annexes ({{ $document->annexes->count() }})
                                    </summary>
                                    <ul class="divide-y divide-gray-100 bg-gray-50">
                                        @foreach ($document->annexes as $annexe)
                                            <li class="flex items-center justify-between pl-10 pr-6 py-3 gap-4 hover:bg-gray-100 transition">
                                                <span class="text-sm text-gray-600">{{ $annexe->title }}</span>
                                                <div class="shrink-0 flex items-center gap-3">
                                                    <a href="{{ route('public.documents.view', $annexe) }}" target="_blank"
                                                       class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-indigo-700 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        Consulter
                                                    </a>
                                                    <a href="{{ route('public.documents.download', $annexe) }}"
                                                       class="inline-flex items-center gap-1 text-xs font-medium text-indigo-500 hover:text-indigo-700 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                                        </svg>
                                                        Télécharger
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($procesVerbaux->isNotEmpty())
            <section>
                <h2 class="text-lg font-semibold text-gray-700 mb-3">Procès-verbaux</h2>
                <ul class="divide-y divide-gray-200 bg-white rounded-xl shadow-sm overflow-hidden">
                    @foreach ($procesVerbaux as $document)
                        <li class="flex items-center justify-between px-6 py-4 gap-4 hover:bg-gray-50 transition">
                            <span class="text-gray-800">{{ $document->title }}</span>
                            <div class="shrink-0 flex items-center gap-3">
                                <a href="{{ route('public.documents.view', $document) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-indigo-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Consulter
                                </a>
                                <a href="{{ route('public.documents.download', $document) }}"
                                   class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    @endif
</x-layouts.public>
