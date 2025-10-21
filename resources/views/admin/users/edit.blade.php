<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modifier l'utilisateur : {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- User Stats -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Statistiques</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Pronostics :</span>
                                <span class="font-semibold text-gray-900">{{ $user->predictions_count }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Inscrit le :</span>
                                <span class="font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <!-- Name -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Nouveau mot de passe
                            </label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                            <p class="mt-1 text-sm text-gray-500">Laisser vide pour ne pas changer le mot de passe</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmer le nouveau mot de passe
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>

                        <!-- Is Admin -->
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="is_admin"
                                    {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Administrateur</span>
                            </label>
                            @if($user->id === auth()->id())
                                <p class="mt-1 text-sm text-orange-600">Vous ne pouvez pas retirer vos propres droits administrateur</p>
                            @else
                                <p class="mt-1 text-sm text-gray-500">Donne accès à l'interface d'administration</p>
                            @endif
                        </div>

                        <!-- Exclude from Leaderboard -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="exclude_from_leaderboard"
                                    {{ old('exclude_from_leaderboard', $user->exclude_from_leaderboard) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700">Exclure du classement</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500">L'utilisateur n'apparaîtra pas dans le leaderboard</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">
                                Annuler
                            </a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
