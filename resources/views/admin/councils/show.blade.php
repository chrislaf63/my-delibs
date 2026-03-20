<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">
                Séance du {{ $council->council_date->format('d/m/Y') }}
                @if($council->reference)
                    <span class="text-gray-500 font-normal text-lg">— {{ $council->reference }}</span>
                @endif
            </h1>
            <a href="{{ route('admin.councils.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Toutes les séances
            </a>
        </div>

        {{-- Formulaire d'ajout --}}
        <div class="bg-white shadow rounded p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Ajouter un document</h2>
            <x-form-upload :council="$council" />
        </div>

        {{-- Liste des documents (temps réel via Livewire) --}}
        <h2 class="text-lg font-semibold mb-3">Documents</h2>
        <div class="bg-white shadow rounded">
            @livewire('admin.council-document-list', ['council' => $council], key('council-'.$council->id))
        </div>

    </div>
</x-app-layout>
