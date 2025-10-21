<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Entrer les Résultats
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-6">Matchs terminés en attente de résultats</p>

                    @if($games->count() > 0)
                        <div class="space-y-6">
                            @foreach($games as $game)
                                <div class="border rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Journée {{ $game->matchday }}</p>
                                            <p class="text-sm text-gray-500">{{ $game->match_date->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex-1 text-center">
                                            <p class="text-lg font-bold">{{ $game->homeTeam->name }}</p>
                                        </div>
                                        <div class="px-8">
                                            <p class="text-gray-500 font-semibold">VS</p>
                                        </div>
                                        <div class="flex-1 text-center">
                                            <p class="text-lg font-bold">{{ $game->awayTeam->name }}</p>
                                        </div>
                                    </div>

                                    <form action="{{ route('admin.results.update', $game) }}" method="POST" class="mt-4">
                                        @csrf
                                        @method('PATCH')

                                        <div class="flex items-center justify-center gap-4">
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Score domicile</label>
                                                <input type="number" name="home_score" min="0" max="20" class="w-20 text-center border-gray-300 rounded-md" required>
                                            </div>
                                            <span class="mt-5">-</span>
                                            <div>
                                                <label class="block text-xs text-gray-600 mb-1">Score extérieur</label>
                                                <input type="number" name="away_score" min="0" max="20" class="w-20 text-center border-gray-300 rounded-md" required>
                                            </div>
                                            <button type="submit" class="mt-5 px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                                                Valider le résultat
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-gray-500">Aucun match en attente de résultats.</p>
                            <p class="text-sm text-gray-400 mt-2">Les matchs terminés apparaîtront automatiquement ici après leur date de match.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
