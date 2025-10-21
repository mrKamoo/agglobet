<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestion des Matchs
            </h2>
            <a href="{{ route('admin.games.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Nouveau Match
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="season_id" class="block text-sm font-medium text-gray-700 mb-2">Filtrer par saison</label>
                            <select id="season_id" onchange="updateFilters()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Toutes les saisons</option>
                                @foreach($seasons as $season)
                                    <option value="{{ $season->id }}" {{ $selectedSeason == $season->id ? 'selected' : '' }}>
                                        {{ $season->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="matchday" class="block text-sm font-medium text-gray-700 mb-2">Filtrer par journée</label>
                            <select id="matchday" onchange="updateFilters()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="">Toutes les journées</option>
                                @foreach($matchdays as $matchday)
                                    <option value="{{ $matchday }}" {{ $selectedMatchday == $matchday ? 'selected' : '' }}>
                                        Journée {{ $matchday }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function updateFilters() {
                    const seasonId = document.getElementById('season_id').value;
                    const matchday = document.getElementById('matchday').value;

                    let url = '{{ route('admin.games.index') }}?';
                    if (seasonId) url += 'season_id=' + seasonId + '&';
                    if (matchday) url += 'matchday=' + matchday;

                    window.location.href = url;
                }
            </script>

            <!-- Games List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($games->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Saison
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Journée
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Domicile
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Score
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Extérieur
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($games as $game)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $game->match_date->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $game->season->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $game->matchday }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center">
                                                    @if($game->homeTeam->logo)
                                                        <img src="{{ $game->homeTeam->logo }}"
                                                             alt="{{ $game->homeTeam->name }}"
                                                             class="h-10 w-10 object-contain"
                                                             title="{{ $game->homeTeam->name }}">
                                                    @else
                                                        <span class="text-sm font-medium text-gray-900">{{ $game->homeTeam->name }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($game->is_finished)
                                                    <span class="text-lg font-bold text-gray-900">{{ $game->home_score }} - {{ $game->away_score }}</span>
                                                @else
                                                    <span class="text-sm text-gray-500">VS</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <div class="flex items-center justify-center">
                                                    @if($game->awayTeam->logo)
                                                        <img src="{{ $game->awayTeam->logo }}"
                                                             alt="{{ $game->awayTeam->name }}"
                                                             class="h-10 w-10 object-contain"
                                                             title="{{ $game->awayTeam->name }}">
                                                    @else
                                                        <span class="text-sm font-medium text-gray-900">{{ $game->awayTeam->name }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-3">
                                                    <a href="{{ route('admin.games.edit', $game) }}"
                                                       class="text-indigo-600 hover:text-indigo-900">
                                                        Modifier
                                                    </a>
                                                    <form action="{{ route('admin.games.destroy', $game) }}"
                                                          method="POST"
                                                          class="inline"
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $games->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucun match enregistré.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
