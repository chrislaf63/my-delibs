<x-layouts.public>
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-800">Recherche de documents</h1>
    </x-slot>

    {{-- Formulaire de recherche --}}
    <form method="GET" action="{{ route('search.index') }}"
          class="bg-white rounded-xl shadow-sm p-5 mb-8 flex flex-col sm:flex-row gap-3">
        <input type="text"
               name="q"
               value="{{ $q }}"
               placeholder="Rechercher un mot-clé..."
               class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />

        <select name="type"
                class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            <option value="">Tous les types</option>
            <option value="deliberation" @selected($type === 'deliberation')>Délibérations</option>
            <option value="proces_verbal" @selected($type === 'proces_verbal')>Procès-verbaux</option>
        </select>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
            </svg>
            Rechercher
        </button>
    </form>

    {{-- Résultats --}}
    @if ($q || $type)
        @if ($documents->isEmpty())
            <p class="text-center text-gray-500 py-10">
                Aucun document ne correspond à votre recherche.
            </p>
        @else
            <p class="text-sm text-gray-500 mb-4">
                {{ $documents->total() }} résultat{{ $documents->total() > 1 ? 's' : '' }}
                @if ($q) pour &laquo;&nbsp;{{ $q }}&nbsp;&raquo; @endif
            </p>

            <ul class="space-y-4">
                @foreach ($documents as $doc)
                    <li class="bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">
                                    {!! $highlightedTitles[$doc->id] ?? e($doc->title) !!}
                                </h3>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    Séance du {{ $doc->council->council_date->translatedFormat('d F Y') }}
                                    &bull;
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $doc->type === 'deliberation' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $doc->type === 'deliberation' ? 'Délibération' : 'Procès-verbal' }}
                                    </span>
                                </p>

                                @if ($q)
                                    @foreach ($contentSnippets[$doc->id] ?? [] as $snippet)
                                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">{!! $snippet !!}</p>
                                    @endforeach
                                    @if (empty($contentSnippets[$doc->id]) && $doc->content)
                                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ mb_substr($doc->content, 0, 200) }}…</p>
                                    @endif
                                @endif
                            </div>

                            <div class="shrink-0 flex items-end gap-5">
                                <a href="{{ route('public.documents.view', $doc) }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    Voir
                                </a>
                                <a href="{{ route('public.documents.download', $doc) }}"
                                   class="inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                {{ $documents->links() }}
            </div>
        @endif
    @else
        <p class="text-center text-gray-400 py-10">
            Saisissez un mot-clé pour lancer la recherche.
        </p>
    @endif
</x-layouts.public>
