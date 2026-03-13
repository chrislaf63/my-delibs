<x-action-section>
    <x-slot name="title">
        {{ __('Supprimer votre compte') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Supprime définitivement votre compte') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('Une fois votre compte supprimé, toutes les ressources et données rattachées seront définitivement effacées.') }}
        </div>

        <div class="mt-5">
            <x-danger-button wire:click="confirmUserDeletion" wire:loading.attr="disabled">
                {{ __('Supprimer votre compte') }}
            </x-danger-button>
        </div>

        <!-- Delete User Confirmation Modal -->
        <x-dialog-modal wire:model.live="confirmingUserDeletion">
            <x-slot name="title">
                {{ __('Supprimer votre compte') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Etes-vous sûr de vouloir supprimer vote compte ? Une fois supprimé, toutes les ressources et données rattachées seront définitivement effacées. Entrez votre mot de passe pour confirmer la suppression') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                                autocomplete="current-password"
                                placeholder="{{ __('Mot de passe') }}"
                                x-ref="password"
                                wire:model="password"
                                wire:keydown.enter="deleteUser" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingUserDeletion')" wire:loading.attr="disabled">
                    {{ __('Annuler') }}
                </x-secondary-button>

                <x-danger-button class="ms-3" wire:click="deleteUser" wire:loading.attr="disabled">
                    {{ __('Supprimer mon compte') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
