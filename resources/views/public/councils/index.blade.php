<x-layouts.public>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-800">Séances du conseil communautaire</h1>
    </x-slot>

    @if ($councilsByYear->isEmpty())
        <p class="text-gray-500 text-center py-12">Aucune séance disponible pour le moment.</p>
    @else
        <div class="space-y-3">
            @foreach ($councilsByYear as $year => $councils)
                <details class="bg-white rounded-xl shadow-sm overflow-hidden group">
                    <summary class="flex items-center justify-between px-6 py-4 cursor-pointer list-none select-none hover:bg-gray-50 transition">
                        <span class="text-lg font-bold text-gray-800">{{ $year }}</span>
                        <span class="text-sm text-ccpl-brown">
                            {{ $councils->count() }} séance{{ $councils->count() > 1 ? 's' : '' }}
                        </span>
                    </summary>

                    <div class="border-t divide-y divide-gray-100">
                        @foreach ($councils as $council)
                            @php $docCount = $council->documents->count(); @endphp
                            <details class="group/council">
                                <summary class="flex items-center justify-between px-6 py-4 cursor-pointer list-none select-none hover:bg-indigo-50 transition">
                                    <div>
                                        <span class="font-semibold text-gray-800">
                                            Séance du {{ $council->council_date->translatedFormat('d F Y') }}
                                        </span>
                                    </div>
                                    @if ($docCount > 0)
                                        <span class="shrink-0 inline-flex items-center rounded-full bg-ccpl-light-green text-ccpl-strong-green text-xs font-medium px-2.5 py-0.5">
                                            {{ $docCount }} document{{ $docCount > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </summary>

                                @if ($docCount > 0)
                                    <ul class="border-t divide-y divide-gray-100 bg-gray-50">
                                        @foreach ($council->documents as $document)
                                            <li>
                                                <div class="flex items-center justify-between px-8 py-3">
                                                    <div class="min-w-0 flex-1 pr-4">
                                                        <p class="text-sm font-medium text-gray-800 truncate" title="{{ $document->title }}">{{ $document->title }}</p>
                                                        <p class="text-xs text-ccpl-brown">
                                                            {{ match($document->type) {
                                                                'deliberation' => 'Délibération',
                                                                'proces_verbal' => 'Procès-verbal',
                                                                'annexe' => 'Annexe',
                                                            } }}
                                                        </p>
                                                    </div>
                                                    <div class="shrink-0 flex items-center gap-3">
                                                        <a href="{{ route('public.documents.view', $document) }}"
                                                           target="_blank"
                                                           class="inline-flex items-center gap-1 text-sm text-ccpl-blue hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            Voir
                                                        </a>
                                                        <a href="{{ route('public.documents.download', $document) }}"
                                                           class="inline-flex items-center gap-1 text-sm text-ccpl-brown hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                                            </svg>
                                                            Télécharger
                                                        </a>
                                                    </div>
                                                </div>

                                                @if($document->type === 'deliberation' && $document->annexes->isNotEmpty())
                                                    <details class="border-t border-gray-100 group/annexe">
                                                        <summary class="flex items-center gap-1.5 px-8 py-2 text-xs font-medium text-ccpl-blue cursor-pointer hover:bg-indigo-50 transition list-none select-none">
                                                            <svg class="h-3.5 w-3.5 transition-transform group-open/annexe:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                            Voir les annexes ({{ $document->annexes->count() }})
                                                        </summary>
                                                        <ul class="divide-y divide-gray-100 bg-white">
                                                            @foreach ($document->annexes as $annexe)
                                                                <li class="flex items-center justify-between pl-12 pr-8 py-3 gap-4 hover:bg-gray-50 transition">
                                                                    <span class="text-sm text-gray-600 min-w-0 flex-1 truncate pr-4" title="{{ $annexe->title }}">{{ $annexe->title }}</span>
                                                                    <div class="shrink-0 flex items-center gap-3">
                                                                        <a href="{{ route('public.documents.view', $annexe) }}" target="_blank"
                                                                           class="inline-flex items-center gap-1 text-xs text-ccpl-blue hover:underline">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                            </svg>
                                                                            Voir
                                                                        </a>
                                                                        <a href="{{ route('public.documents.download', $annexe) }}"
                                                                           class="inline-flex items-center gap-1 text-xs text-ccpl-brown hover:underline">
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
                                @else
                                    <p class="px-8 py-3 text-sm text-gray-400 bg-gray-50 border-t">Aucun document disponible.</p>
                                @endif
                            </details>
                        @endforeach
                    </div>
                </details>
            @endforeach
        </div>
    @endif
</x-layouts.public>
