<x-app-layout>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-xl font-semibold mb-6">Créer un utilisateur</h1>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div>
                <label for="first_name">Prénom</label>
                <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('first_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="name">Nom</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="email">Adresse e-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <label for="password_confirmation">Confirmation du mot de passe</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <button type="submit" class="mt-6 bg-blue-600 text-white px-4 py-2 rounded">
                Créer
            </button>
        </form>
    </div>
</x-app-layout>
