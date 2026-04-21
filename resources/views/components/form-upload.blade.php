@props(['council'])

@php
    $uid = uniqid('upload_');
    $deliberations = $council->documents()->where('type', 'deliberation')->orderBy('title')->get();
@endphp

<form method="POST"
      action="{{ route('admin.documents.store', $council) }}"
      enctype="multipart/form-data"
      id="{{ $uid }}_form">

    @csrf

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select name="type" required id="{{ $uid }}_type"
                    class="w-full border border-gray-300 rounded p-2 text-sm">
                <option value="deliberation">Délibération</option>
                <option value="proces_verbal">Procès-verbal</option>
                <option value="annexe">Annexe</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
            <input type="text" name="title" id="{{ $uid }}_title" placeholder="Titre du document"
                   class="w-full border border-gray-300 rounded p-2 text-sm">
        </div>
    </div>

    <div class="mb-4" id="{{ $uid }}_parent_row" style="display:none">
        <label class="block text-sm font-medium text-gray-700 mb-1">Délibération parente</label>
        <select name="parent_document_id"
                class="w-full border border-gray-300 rounded p-2 text-sm">
            <option value="">— Choisir une délibération —</option>
            @foreach ($deliberations as $delib)
                <option value="{{ $delib->id }}">{{ $delib->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Fichier PDF</label>

        <div id="{{ $uid }}_zone"
             class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors"
             onclick="document.getElementById('{{ $uid }}_input').click()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="text-sm text-gray-500 truncate" id="{{ $uid }}_label">
                Glissez un fichier PDF ici ou <span class="text-blue-600 underline">cliquez pour parcourir</span>
            </p>
        </div>

        <input type="file" name="file" accept="application/pdf" required
               id="{{ $uid }}_input"
               class="hidden">
    </div>

    <button type="submit"
            class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
        Ajouter le document
    </button>

</form>

<script>
(function () {
    const typeSelect = document.getElementById('{{ $uid }}_type');
    const parentRow  = document.getElementById('{{ $uid }}_parent_row');

    typeSelect.addEventListener('change', function () {
        parentRow.style.display = this.value === 'annexe' ? '' : 'none';
    });

    const zone       = document.getElementById('{{ $uid }}_zone');
    const input      = document.getElementById('{{ $uid }}_input');
    const label      = document.getElementById('{{ $uid }}_label');
    const titleInput = document.getElementById('{{ $uid }}_title');

    function setFile(file) {
        if (!file || file.type !== 'application/pdf') return;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        label.innerHTML = '<span class="font-medium text-green-700">' + file.name + '</span>';
        zone.classList.remove('border-gray-300', 'hover:border-blue-400', 'hover:bg-blue-50');
        zone.classList.add('border-green-400', 'bg-green-50');
        if (titleInput.value === '') {
            titleInput.value = file.name.replace(/\.[^.]+$/, '');
        }
    }

    input.addEventListener('change', function () {
        if (input.files.length) setFile(input.files[0]);
    });

    zone.addEventListener('dragover', function (e) {
        e.preventDefault();
        zone.classList.add('border-blue-400', 'bg-blue-50');
    });

    zone.addEventListener('dragleave', function () {
        if (!input.files.length) {
            zone.classList.remove('border-blue-400', 'bg-blue-50');
        }
    });

    zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('border-blue-400', 'bg-blue-50');
        const file = e.dataTransfer.files[0];
        setFile(file);
    });
})();
</script>
