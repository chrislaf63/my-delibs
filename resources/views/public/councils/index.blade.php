<x-layouts.public>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-800">Séances du conseil communautaire</h1>
    </x-slot>

    @if ($councilsByYear->isEmpty())
        <p class="text-gray-500 text-center py-12">Aucune séance disponible pour le moment.</p>
    @else
        <div class="space-y-3">
            @foreach ($councilsByYear as $year => $councils)
                <details class="bg-white rounded-xl shadow-sm overflow-hidden group" open>
                    <summary class="flex items-center justify-between px-6 py-4 cursor-pointer list-none select-none hover:bg-gray-50 transition">
                        <span class="text-lg font-bold text-gray-800">{{ $year }}</span>
                        <span class="text-sm text-gray-400">
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
                                        <span class="shrink-0 inline-flex items-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-medium px-2.5 py-0.5">
                                            {{ $docCount }} document{{ $docCount > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </summary>

                                @if ($docCount > 0)
                                    <ul class="border-t divide-y divide-gray-100 bg-gray-50">
                                        @foreach ($council->documents as $document)
                                            <li>
                                                <div class="flex items-center justify-between px-8 py-3">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-800">{{ $document->title }}</p>
                                                        <p class="text-xs text-gray-400">
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
                                                           class="text-sm text-indigo-600 hover:underline">
                                                            Voir
                                                        </a>
                                                        <a href="{{ route('public.documents.download', $document) }}"
                                                           class="text-sm text-gray-500 hover:underline">
                                                            Télécharger
                                                        </a>
                                                    </div>
                                                </div>

                                                @if($document->type === 'deliberation' && $document->annexes->isNotEmpty())
                                                    <details class="border-t border-gray-100 group/annexe">
                                                        <summary class="flex items-center gap-1.5 px-8 py-2 text-xs font-medium text-indigo-600 cursor-pointer hover:bg-indigo-50 transition list-none select-none">
                                                            <svg class="h-3.5 w-3.5 transition-transform group-open/annexe:rotate-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                            Voir les annexes ({{ $document->annexes->count() }})
                                                        </summary>
                                                        <ul class="divide-y divide-gray-100 bg-white">
                                                            @foreach ($document->annexes as $annexe)
                                                                <li class="flex items-center justify-between pl-12 pr-8 py-3 gap-4 hover:bg-gray-50 transition">
                                                                    <span class="text-sm text-gray-600">{{ $annexe->title }}</span>
                                                                    <div class="shrink-0 flex items-center gap-3">
                                                                        <a href="{{ route('public.documents.view', $annexe) }}" target="_blank"
                                                                           class="text-xs text-indigo-600 hover:underline">Voir</a>
                                                                        <a href="{{ route('public.documents.download', $annexe) }}"
                                                                           class="text-xs text-gray-500 hover:underline">Télécharger</a>
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
