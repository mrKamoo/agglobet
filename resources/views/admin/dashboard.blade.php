<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Administrateur
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Utilisateurs</div>
                        <div class="text-3xl font-bold">{{ $stats['users'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Pronostics</div>
                        <div class="text-3xl font-bold">{{ $stats['predictions'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Saisons</div>
                        <div class="text-3xl font-bold">{{ $stats['seasons'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-gray-500 text-sm">Matchs</div>
                        <div class="text-3xl font-bold">{{ $stats['games'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Active Season Info -->
            @if($stats['active_season'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2">Saison active</h3>
                        <p class="text-gray-700">{{ $stats['active_season']->name }}</p>
                        <p class="text-sm text-gray-500">
                            Du {{ $stats['active_season']->start_date->format('d/m/Y') }}
                            au {{ $stats['active_season']->end_date->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                    <p>Aucune saison active. Créez ou activez une saison pour commencer.</p>
                    <a href="{{ route('admin.seasons.create') }}" class="underline">Créer une saison</a>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Actions rapides</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('admin.seasons.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Créer une saison
                        </a>
                        <a href="{{ route('admin.games.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                            Ajouter un match
                        </a>
                        <a href="{{ route('admin.results.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                            Entrer des résultats
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
