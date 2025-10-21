<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Synchronisation Football-Data.org
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Active Season Info -->
            @if($activeSeason)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Saison Active</h3>
                    <p class="text-blue-800">{{ $activeSeason->name }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        Du {{ $activeSeason->start_date->format('d/m/Y') }} au {{ $activeSeason->end_date->format('d/m/Y') }}
                    </p>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-2">Aucune saison active</h3>
                    <p class="text-yellow-800">Veuillez créer et activer une saison avant de synchroniser les matchs.</p>
                    <a href="{{ route('admin.seasons.index') }}" class="inline-block mt-3 text-yellow-700 hover:text-yellow-900 underline">
                        Gérer les saisons
                    </a>
                </div>
            @endif

            <!-- Sync Teams -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Synchroniser les Équipes</h3>
                    <p class="text-gray-600 mb-4">
                        Importe ou met à jour toutes les équipes de la Ligue 1 (noms, logos, stades, etc.)
                    </p>
                    <form action="{{ route('admin.sync.teams') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Synchroniser les Équipes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sync Matches -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Synchroniser les Matchs</h3>
                    <p class="text-gray-600 mb-4">
                        Importe tous les matchs de la Ligue 1 pour la saison sélectionnée. Les matchs existants seront mis à jour avec les derniers scores.
                    </p>

                    <form action="{{ route('admin.sync.matches') }}" method="POST">
                        @csrf

                        <!-- Season Selection -->
                        <div class="mb-4">
                            <label for="season_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Saison à synchroniser
                            </label>
                            <select name="season_id" id="season_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Saison active{{ $activeSeason ? ' (' . $activeSeason->name . ')' : '' }}</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ $season->is_active ? 'selected' : '' }}>
                                        {{ $season->name }} {{ $season->is_active ? '(Active)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Synchroniser les Matchs
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Comment ça marche ?</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-700">
                    <li>Les matchs sont importés depuis l'API gratuite <strong>Football-Data.org</strong></li>
                    <li>Les matchs existants sont mis à jour (scores, statuts)</li>
                    <li>Les nouveaux matchs sont automatiquement créés</li>
                    <li>Les équipes manquantes sont créées automatiquement</li>
                    <li>Limite de l'API gratuite: <strong>10 requêtes/minute</strong></li>
                </ul>

                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-sm text-yellow-800">
                        <strong>Note:</strong> Assurez-vous d'avoir configuré votre clé API dans le fichier <code class="bg-yellow-100 px-1 rounded">.env</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
