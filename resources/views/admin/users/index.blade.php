<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Gestion des utilisateurs</h1>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-ccpl-blue opacity-80 text-white text-sm font-medium rounded hover:opacity-100 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvel utilisateur
            </a>
        </div>

        @if(session('status'))
            <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-4 py-3 rounded bg-red-100 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Créé le</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->first_name }} {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="ml-2 text-xs text-gray-400">(vous)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->translatedFormat('j F Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                @if($users->count() > 1)
                                    <button type="button"
                                            onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->first_name . ' ' . $user->name) }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded text-white bg-red-600 hover:bg-red-700 transition"
                                            title="Supprimer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400 italic">Seul utilisateur</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>

    {{-- Modale de confirmation — suppression utilisateur --}}
    <div id="user-delete-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Supprimer l'utilisateur</h2>
            <p class="text-sm text-gray-600 mb-1">Voulez-vous vraiment supprimer :</p>
            <p id="user-delete-modal-name" class="text-sm font-medium text-gray-900 mb-4"></p>
            <p class="text-sm text-red-600 mb-6">Cette action est irréversible.</p>

            <form id="user-delete-form" method="POST">
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

    <script>
        const userModal = document.getElementById('user-delete-modal');
        const userForm  = document.getElementById('user-delete-form');
        const userName  = document.getElementById('user-delete-modal-name');
        const userBase  = '{{ rtrim(route("admin.users.destroy", ["user" => "__ID__"]), "") }}';

        function openDeleteModal(id, name) {
            userForm.action = userBase.replace('__ID__', id);
            userName.textContent = name;
            userModal.classList.remove('hidden');
            userModal.classList.add('flex');
        }

        function closeDeleteModal() {
            userModal.classList.add('hidden');
            userModal.classList.remove('flex');
        }

        userModal.addEventListener('click', function (e) {
            if (e.target === userModal) closeDeleteModal();
        });
    </script>
</x-app-layout>
